<?php

namespace CodeJetter\components\user\controllers;

use CodeJetter\components\user\mappers\GroupMemberUserXrefMapper;
use CodeJetter\components\user\mappers\MemberGroupMapper;
use CodeJetter\components\user\mappers\MemberUserMapper;
use CodeJetter\components\user\models\MemberGroup;
use CodeJetter\components\user\models\MemberUser;
use CodeJetter\core\BaseController;
use CodeJetter\core\FormHandler;
use CodeJetter\core\io\Input;
use CodeJetter\core\io\Output;
use CodeJetter\core\io\Request;
use CodeJetter\core\io\Response;
use CodeJetter\core\layout\blocks\ComponentTemplate;
use CodeJetter\core\layout\blocks\Pager;
use CodeJetter\core\Registry;
use CodeJetter\core\security\Validator;
use CodeJetter\core\security\ValidatorRule;
use CodeJetter\core\utility\DateTimeUtility;
use CodeJetter\core\utility\MysqlUtility;
use CodeJetter\core\View;
use CodeJetter\components\page\models\Page;
use CodeJetter\libs\TableGenerator\HeadCell;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Class MemberUserController
 * @package CodeJetter\components\user\controllers
 */
class MemberUserController extends BaseController
{
    /**
     * Generate register form
     *
     * @throws \Exception
     */
    public function registerForm()
    {
        $page = new Page($this->getRouteInfo()->getAccessRole());
        $page->setTitle('Register');

        /**
         * hi to language
         */
        $language = Registry::getLanguageClass();
        $requiredFields = $language->get('requiredFields');
        $passwordRequirements = $language->get('passwordRequirements');
        $usernameRequirements = $language->get('usernameRequirements');
        /**
         * bye to language
         */

        $componentTemplate = new ComponentTemplate();
        $componentTemplate->setTemplatePath($this->getTemplatesPath() . 'memberRegister.php');
        $componentTemplate->setData([
            'requiredFields' => $requiredFields,
            'passwordRequirements' => $passwordRequirements,
            'usernameRequirements' => $usernameRequirements
        ]);

        (new View())->make(
            $page,
            [
                'register' => $componentTemplate
            ],
            null,
            new FormHandler('register')
        );
    }

    /**
     * Register a new user
     *
     * @throws \Exception
     */
    public function register()
    {
        $inputs = (new Request('POST'))->getInputs();

        $inputs['parentId'] = 0;
        $inputs['status'] = 'active';
        $inputs['passwordRequired'] = true;

        $output = (new MemberUserMapper())->add($inputs);

        if ($output->getSuccess() === true) {
            // On successful register, login automatically
            $output = (new MemberUser())->login($inputs['username'], $inputs['password']);
        }

        (new Response())->echoContent($output->toJSON());
    }

    /**
     * Generate login form
     *
     * @throws \Exception
     */
    public function loginForm()
    {
        $page = new Page($this->getRouteInfo()->getAccessRole());
        $page->setTitle('Login');

        /**
         * hi to language
         */
        $requiredFields = Registry::getLanguageClass()->get('requiredFields');
        /**
         * bye to language
         */

        $componentTemplate = new ComponentTemplate();
        $componentTemplate->setTemplatePath($this->getTemplatesPath() . 'login.php');
        $componentTemplate->setData([
            'requiredFields' => $requiredFields,
            'url' => '/login',
            'forgotPasswordUrl' => '/forgot-password'
        ]);

        (new View())->make(
            $page,
            [
                'loginMember' => $componentTemplate
            ],
            null,
            new FormHandler('Login')
        );
    }

    /**
     * Login
     */
    public function login()
    {
        $inputs = (new Request('POST'))->getInputs();

        if (isset($inputs['username']) && isset($inputs['password'])) {
            $output = (new MemberUser())->login($inputs['username'], $inputs['password']);

            // redirection is happening in javascript
            (new Response())->echoContent($output->toJSON());
        }
    }

    /**
     * @return \CodeJetter\core\io\Output
     */
    public function logout()
    {
        return (new MemberUser())->logout();
    }

    /**
     * @throws \Exception
     */
    public function listUsers()
    {
        // for pagination - for simple router url path is enough, for regex ones base path is needed
        $pager = new Pager($this->getURLParameters(), $this->getBasePath(), $this->getRouteInfo()->getUrl());

        /**
         * If the column in the database is not lowercase of the title, specify alias
         */
        $numberCell = new HeadCell('#');
        $numberCell->setSortable(false);
        $numberCell->setSearchable(false);

        $actionsCell = new HeadCell('Actions');
        $actionsCell->setSortable(false);
        $actionsCell->setSearchable(false);

        $listHeaders = [
            $numberCell,
            new HeadCell('Username'),
            new HeadCell('Name'),
            new HeadCell('Email'),
            new HeadCell('Phone'),
            new HeadCell('Status'),
            $actionsCell
        ];

        // get order and search query from the query string
        $listConfig = Registry::getConfigClass()->get('list');
        $request = new Request();
        $searchQuery = $request->getQueryStringVariables($listConfig['query']);
        $order = $request->getSortingFromQueryString();
        $criteria = (new MysqlUtility())->generateSearchCriteria($listHeaders);

        // get children of this member
        $output = (new MemberUserMapper())->getChildren(
            $criteria,
            $order,
            $pager->getStart(),
            $pager->getLimit(),
            true
        );

        if ($output->getSuccess() !== true) {
            return false;
        }

        $children = $output->getData();

        /**
         * If the current page is not page 1, and there is no data,
         * set pager to page 1 and get data again with start 0
         */
        if (empty($children['result']) && $children['total'] > 0 && $pager->getCurrentPage() > 1) {
            $pager->setCurrentPage(1);
            $output = (new MemberUserMapper())->getChildren($criteria, $order, 0, $pager->getLimit(), true);

            if ($output->getSuccess() !== true) {
                return false;
            }

            $children = $output->getData();
        }

        $pager->setTotal($children['total']);

        /**
         * hi to language
         */
        $language = Registry::getLanguageClass();
        $requiredFields = $language->get('requiredFields');
        $passwordRequirements = $language->get('passwordRequirements');
        $usernameRequirements = $language->get('usernameRequirements');
        /**
         * bye to language
         */

        // create component
        $componentTemplate = new ComponentTemplate();
        $componentTemplate->setTemplatePath($this->getTemplatesPath() . 'membersList.php');
        $componentTemplate->setPager($pager);
        $componentTemplate->setData([
            'listHeaders' => $listHeaders,
            'children' => $children,
            'searchQueryKey' => $listConfig['query'],
            'searchQuery' => $searchQuery,
            'requiredFields' => $requiredFields,
            'passwordRequirements' => $passwordRequirements,
            'usernameRequirements' => $usernameRequirements
        ]);

        // create the page for view
        $page = new Page($this->getRouteInfo()->getAccessRole());
        $page->setTitle('Members');
        $page->setIntro('Here you can manage your members');

        (new View())->make(
            $page,
            [
                'members' => $componentTemplate
            ],
            null,
            new FormHandler('List Members')
        );
    }

    /**
     * Add a child
     */
    public function addChild()
    {
        $inputs = (new Request('POST'))->getInputs();

        $inputs['parentId'] = (new MemberUser())->getLoggedIn()->getId();

        $output = (new MemberUserMapper())->add($inputs);

        (new Response())->echoContent($output->toJSON());
    }

    /**
     * Update a child's info
     */
    public function updateChild()
    {
        $inputs = (new Request('POST'))->getInputs();

        if (isset($inputs['id'])) {
            $output = (new MemberUserMapper())->updateChildById($inputs['id'], $inputs);

            echo $output->toJSON();
        }
    }

    /**
     * Display forgot password form
     *
     * @throws \Exception
     */
    public function forgotPasswordForm()
    {
        $page = new Page($this->getRouteInfo()->getAccessRole());
        $page->setTitle('Forgot your password');

        /**
         * hi to language
         */
        $requiredFields = Registry::getLanguageClass()->get('requiredFields');
        /**
         * bye to language
         */

        $componentTemplate = new ComponentTemplate();
        $componentTemplate->setTemplatePath($this->getTemplatesPath() . 'memberForgotPassword.php');
        $componentTemplate->setData([
            'formUrl' => '/forgot-password',
            'requiredFields' => $requiredFields
        ]);

        (new View())->make(
            $page,
            [
                'forgotPassword' => $componentTemplate
            ],
            null,
            new FormHandler('forgotPasswordForm')
        );
    }

    /**
     * @return Output
     * @throws \Exception
     */
    public function forgotPassword()
    {
        // get email
        $inputs = (new Request('POST'))->getInputs(['email']);
        $output = (new MemberUser())->forgotPassword($inputs['email']);

        (new Response())->echoContent($output->toJSON());
    }

    /**
     * Display reset password form
     *
     * @throws \Exception
     */
    public function resetPasswordForm()
    {
        $inputs = $this->getURLParameters();
        $output = (new MemberUser())->checkTokenIsValidByEmail($inputs['email'], $inputs['token']);

        $page = new Page($this->getRouteInfo()->getAccessRole());
        $page->setTitle('Reset your password');

        $componentTemplate = new ComponentTemplate();
        $componentTemplate->setTemplatePath($this->getTemplatesPath() . 'memberResetPassword.php');
        $componentTemplate->setData([
            'tokenValid' => $output->getSuccess(),
            'email' => $inputs['email'],
            'token' => $inputs['token'],
            'formUrl' => '/reset-password'
        ]);

        (new View())->make(
            $page,
            [
                'passwordReset' => $componentTemplate
            ],
            null,
            new FormHandler('Password Reset')
        );
    }

    /**
     * Reset a member password
     */
    public function resetPassword()
    {
        $inputs = (new Request('POST'))->getInputs(['email', 'resetPasswordToken', 'password', 'passwordConfirmation']);
        $output = (new MemberUser())->resetPassword(
            $inputs['email'],
            $inputs['resetPasswordToken'],
            $inputs['password'],
            $inputs['passwordConfirmation']
        );

        (new Response())->echoContent($output->toJSON());
    }

    /**
     * @throws \Exception
     */
    public function viewChild()
    {
        $urlParameters = $this->getURLParameters();

        if (empty($urlParameters['id'])) {
            return false;
        }

        /**
         * Start getting all the groups (not assigned ones)
         */
        // get all the available groups
        $criteria = [
            [
                'column' => 'status',
                'value' => 'active',
                'type' => \PDO::PARAM_INT
            ]
        ];

        $groups = (new MemberGroupMapper())->getAll($criteria, [], 'name ASC');

        // extract option name and values
        $groupValues = [];
        if (!empty($groups)) {
            foreach ($groups as $group) {
                if (!$group instanceof MemberGroup) {
                    continue;
                }

                $groupValues[$group->getName()] = $group->getId();
            }
        }
        /**
         * Finish getting all the groups (not assigned ones)
         */

        $output = (new MemberUserMapper())->getOneById($urlParameters['id']);

        $member = $output->getData();

        /**
         * Start checking parent has got this child
         */
        $parent = (new MemberUser())->getLoggedIn();
        if ($member instanceof MemberUser && $parent instanceof MemberUser) {
            if ($parent->getId() != $member->getParentId()) {
                $member = '';
            }
        } else {
            $member = '';
        }
        /**
         * Finish checking parent has got this child
         */

        $statuses = (new MemberUserMapper())->getEnumValues('status');

        /**
         * hi to language
         */
        $language = Registry::getLanguageClass();
        $requiredFields = $language->get('requiredFields');
        $passwordRequirements = $language->get('passwordRequirements');
        $usernameRequirements = $language->get('usernameRequirements');
        /**
         * bye to language
         */

        $componentTemplate = new ComponentTemplate();
        $componentTemplate->setTemplatePath($this->getTemplatesPath() . 'memberView.php');

        $timeZoneList = (new DateTimeUtility())->getTimeZones();

        $componentTemplate->setData([
            'member' => $member,
            'statuses' => $statuses,
            'groups' => $groupValues,
            'updateFormUrl' => '/account/update-child',
            'timeZoneList' => $timeZoneList,
            'requiredFields' => $requiredFields,
            'passwordRequirements' => $passwordRequirements,
            'usernameRequirements' => $usernameRequirements
        ]);

        // create the page for view
        $page = new Page($this->getRouteInfo()->getAccessRole());
        $page->setTitle('View Member');

        (new View())->make(
            $page,
            [
                'member' => $componentTemplate
            ],
            null,
            new FormHandler('View Member')
        );
    }

    /**
     * @throws \Exception
     */
    public function safeDeleteChild()
    {
        $inputs = (new Request('POST'))->getInputs();

        if (empty($inputs['id'])) {
            return false;
        }

        $currentUser = (new MemberUser())->getLoggedIn();

        $output = (new MemberUserMapper())->safeDeleteOne([
            [
                'column' => 'id',
                'value' => $inputs['id'],
                'type' => \PDO::PARAM_INT
            ],
            [
                'column' => 'parentId',
                'value' => $currentUser->getId(),
                'type' => \PDO::PARAM_INT
            ]
        ]);

        if ($output > 0) {
            // delete group xrefs if user is deleted
            // safe delete the group xrefs first, if there is any
            (new GroupMemberUserXrefMapper())->safeDelete([
                [
                    'column' => 'memberId',
                    'value' => $inputs['id'],
                    'type' => \PDO::PARAM_INT
                ]
            ]);

            $output = new Output();
            $output->setSuccess(true);
            $output->setMessage('Member safely deleted');

            (new Response())->echoContent($output->toJSON());
        }
    }

    /**
     * @throws \Exception
     */
    public function safeBatchDeleteChild()
    {
        $inputs = (new Request('POST'))->getInputs();

        /**
         * Start validating
         */
        $output = new Output();
        if (empty($inputs['callback'])) {
            $output->setSuccess(false);
            $output->setMessage('Please select members first');

            (new Response())->echoContent($output->toJSON());
        }

        // convert to array if it is not array
        if (!is_array($inputs['callback'])) {
            $inputs['callback'] = explode(',', $inputs['callback']);
        }

        $requiredRule = new ValidatorRule('required');
        $idRule = new ValidatorRule('id');

        $idInput = new Input('id', [$requiredRule, $idRule]);

        $definedInputs = [
            $idInput
        ];

        foreach ($inputs['callback'] as $id) {
            $validatorOutput = (new Validator($definedInputs, ['id' => $id]))->validate();

            if ($validatorOutput->getSuccess() !== true) {
                $output->setSuccess(false);
                $output->setMessages($validatorOutput->getMessages());

                (new Response())->echoContent($output->toJSON());
            }
        }
        /**
         * Finish validating
         */

        $currentUser = (new MemberUser())->getLoggedIn();

        $output = (new MemberUserMapper())->safeDelete(
            [
                [
                    'column' => 'id',
                    'operator' => 'IN',
                    'value' => $inputs['callback'],
                    'type' => \PDO::PARAM_INT
                ],
                [
                    'column' => 'parentId',
                    'value' => $currentUser->getId(),
                    'type' => \PDO::PARAM_INT
                ]
            ],
            count($inputs['callback'])
        );

        if ($output instanceof Output and (int) $output->getData() > 0) {
            // delete group xrefs if user is deleted
            // safe delete the group xrefs first, if there is any
            (new GroupMemberUserXrefMapper())->safeDelete([
                [
                    'column' => 'memberId',
                    'operator' => 'IN',
                    'value' => $inputs['callback'],
                    'type' => \PDO::PARAM_INT
                ]
            ]);

            $output = new Output();
            $output->setSuccess(true);
            $output->setMessage('Member(s) safely deleted');

            (new Response())->echoContent($output->toJSON());
        }
    }

    /**
     * @throws \Exception
     */
    public function profileForm()
    {
        $currentUser = (new MemberUser())->getLoggedIn();

        /**
         * hi to language
         */
        $language = Registry::getLanguageClass();
        $requiredFields = $language->get('requiredFields');
        $passwordRequirements = $language->get('passwordRequirements');
        $usernameRequirements = $language->get('usernameRequirements');
        /**
         * bye to language
         */

        $componentTemplate = new ComponentTemplate();
        $componentTemplate->setTemplatePath($this->getTemplatesPath() . 'profile.php');

        $timeZoneList = (new DateTimeUtility())->getTimeZones();

        $componentTemplate->setData([
            'member' => $currentUser,
            'updateFormUrl' => '/account/update-profile',
            'timeZoneList' => $timeZoneList,
            'requiredFields' => $requiredFields,
            'passwordRequirements' => $passwordRequirements,
            'usernameRequirements' => $usernameRequirements
        ]);

        // create the page for view
        $page = new Page($this->getRouteInfo()->getAccessRole());
        $page->setTitle('Your Profile');

        (new View())->make(
            $page,
            [
                'member' => $componentTemplate
            ],
            null,
            new FormHandler('Your Profile')
        );
    }

    /**
     * Update a profile (current member) info
     */
    public function updateProfile()
    {
        $currentUser = (new MemberUser())->getLoggedIn();

        $inputs = (new Request('POST'))->getInputs();

        if ($currentUser instanceof MemberUser) {
            $output = (new MemberUserMapper())->updateById($currentUser->getId(), $inputs);

            echo $output->toJSON();
        }
    }
}