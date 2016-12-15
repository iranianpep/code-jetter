<?php

namespace CodeJetter\components\user\controllers;

use CodeJetter\components\page\models\Page;
use CodeJetter\components\user\mappers\AdminUserMapper;
use CodeJetter\components\user\mappers\GroupMemberUserXrefMapper;
use CodeJetter\components\user\mappers\MemberGroupMapper;
use CodeJetter\components\user\mappers\MemberUserMapper;
use CodeJetter\components\user\models\AdminUser;
use CodeJetter\components\user\models\MemberGroup;
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
use TableGenerator\HeadCell;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Class AdminUserController.
 */
class AdminUserController extends BaseController
{
    /**
     * Generate login form.
     *
     * @throws \Exception
     */
    public function loginForm()
    {
        $page = new Page($this->getRouteInfo()->getAccessRole());
        $page->setTitle('Login');

        /**
         * hi to language.
         */
        $requiredFields = Registry::getLanguageClass()->get('requiredFields');
        /**
         * bye to language.
         */
        $componentTemplate = new ComponentTemplate();
        $componentTemplate->setTemplatePath($this->getTemplatesPath().'login.php');
        $componentTemplate->setData([
            'requiredFields'    => $requiredFields,
            'url'               => '/admin/login',
            'forgotPasswordUrl' => '/admin/forgot-password',
        ]);

        (new View())->make(
            $page,
            [
                'loginMember' => $componentTemplate,
            ],
            null,
            new FormHandler('Login')
        );
    }

    /**
     * login.
     */
    public function login()
    {
        $inputs = (new Request('POST'))->getInputs();

        if (isset($inputs['username']) && isset($inputs['password'])) {
            $output = (new AdminUser())->login('username', $inputs['username'], $inputs['password']);

            // redirection is happening in javascript
            (new Response())->echoContent($output->toJSON());
        }
    }

    /**
     * @throws \Exception
     */
    public function listMembers()
    {
        // for pagination - for simple router url path is enough, for regex ones base path is needed
        $pager = new Pager($this->getURLParameters(), $this->getBasePath(), $this->getRouteInfo()->getUrl());

        /**
         * If the column in the database is not lowercase of the title, specify alias.
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
            $actionsCell,
        ];

        // get order and search query from the query string
        $listConfig = Registry::getConfigClass()->get('list');
        $request = new Request();
        $searchQuery = $request->getQueryStringVariables($listConfig['query']);
        $order = $request->getSortingFromQueryString();
        $criteria = (new MysqlUtility())->generateSearchCriteria($listHeaders);

        $members = (new MemberUserMapper())->getAll(
            $criteria,
            [],
            $order,
            $pager->getStart(),
            $pager->getLimit(),
            true
        );

        /*
         * If the current page is not page 1, and there is no data,
         * set pager to page 1 and get data again with start 0
         */
        if (empty($members['result']) && $members['total'] > 0 && $pager->getCurrentPage() > 1) {
            $pager->setCurrentPage(1);
            $members = (new MemberUserMapper())->getAll($criteria, [], $order, 0, $pager->getLimit(), true);
        }

        $pager->setTotal($members['total']);
        $pager->setCurrentPageResultNo(count($members['result']));

        /**
         * hi to language.
         */
        $language = Registry::getLanguageClass();
        $requiredFields = $language->get('requiredFields');
        $passwordRequirements = $language->get('passwordRequirements');
        $usernameRequirements = $language->get('usernameRequirements');
        /**
         * bye to language.
         */

        // create component
        $componentTemplate = new ComponentTemplate();
        $componentTemplate->setTemplatePath($this->getTemplatesPath().'adminMembersList.php');
        $componentTemplate->setPager($pager);
        $componentTemplate->setData([
            'listHeaders'          => $listHeaders,
            'members'              => $members['result'],
            'searchQueryKey'       => $listConfig['query'],
            'searchQuery'          => $searchQuery,
            'requiredFields'       => $requiredFields,
            'passwordRequirements' => $passwordRequirements,
            'usernameRequirements' => $usernameRequirements,
        ]);

        // create the page for view
        $page = new Page($this->getRouteInfo()->getAccessRole());
        $page->setTitle('Members');
        $page->setIntro('Here you can manage your members');
        //$page->setCategory('User Management');

        (new View())->make(
            $page,
            [
                'members' => $componentTemplate,
            ],
            null,
            new FormHandler('List Members')
        );
    }

    /**
     * @return \CodeJetter\core\io\Output
     */
    public function logout()
    {
        return (new AdminUser())->logout();
    }

    /**
     * Add a new member.
     */
    public function addMember()
    {
        $inputs = (new Request('POST'))->getInputs();
        $inputs['parentId'] = 0;

        $output = (new MemberUserMapper())->add($inputs);

        (new Response())->echoContent($output->toJSON());
    }

    /**
     * Safely delete a member.
     *
     * @throws \Exception
     */
    public function safeDeleteMember()
    {
        $inputs = (new Request('POST'))->getInputs();

        if (!empty($inputs['id'])) {
            $output = (new MemberUserMapper())->safeDeleteOne([
                [
                    'column' => 'id',
                    'value'  => $inputs['id'],
                    'type'   => \PDO::PARAM_INT,
                ],
            ]);

            if ($output > 0) {
                // delete group xrefs if user is deleted
                // safe delete the group xrefs first, if there is any
                (new GroupMemberUserXrefMapper())->safeDelete([
                    [
                        'column' => 'memberId',
                        'value'  => $inputs['id'],
                        'type'   => \PDO::PARAM_INT,
                    ],
                ]);

                $output = new Output();
                $output->setSuccess(true);
                $output->setMessage('Member safely deleted');

                (new Response())->echoContent($output->toJSON());
            }
        }
    }

    /**
     * Safely batch delete members.
     *
     * @throws \Exception
     */
    public function safeBatchDeleteMember()
    {
        $inputs = (new Request('POST'))->getInputs();

        /**
         * Start validating.
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
            $idInput,
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
         * Finish validating.
         */
        $output = (new MemberUserMapper())->safeDelete(
            [
                [
                    'column'   => 'id',
                    'operator' => 'IN',
                    'value'    => $inputs['callback'],
                    'type'     => \PDO::PARAM_INT,
                ],
            ],
            count($inputs['callback'])
        );

        if ($output instanceof Output and (int) $output->getData() > 0) {
            // delete group xrefs if user is deleted
            // safe delete the group xrefs first, if there is any
            (new GroupMemberUserXrefMapper())->safeDelete([
                [
                    'column'   => 'memberId',
                    'operator' => 'IN',
                    'value'    => $inputs['callback'],
                    'type'     => \PDO::PARAM_INT,
                ],
            ]);

            $output = new Output();
            $output->setSuccess(true);
            $output->setMessage('Member(s) safely deleted');

            (new Response())->echoContent($output->toJSON());
        }
    }

    /**
     * Delete a member.
     *
     * @throws \Exception
     */
    public function deleteMember()
    {
        $inputs = (new Request('POST'))->getInputs();

        if (!empty($inputs['id'])) {
            /**
             * Do not need to delete xref, since foreign key is set to CASCADE on delete.
             */
            $output = (new MemberUserMapper())->deleteOne([
                [
                    'column' => 'id',
                    'value'  => $inputs['id'],
                    'type'   => \PDO::PARAM_INT,
                ],
            ]);

            if ($output === true) {
                $output = new Output();
                $output->setSuccess(true);
                $output->setMessage('Member deleted successfully');

                (new Response())->echoContent($output->toJSON());
            }
        }
    }

    /**
     * Batch delete members.
     *
     * @throws \Exception
     */
    public function batchDeleteMember()
    {
        $inputs = (new Request('POST'))->getInputs();

        /**
         * Start validating.
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
            $idInput,
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
         * Finish validating.
         */

        /**
         * Do not need to delete xref, since foreign key is set to CASCADE on delete.
         */
        $output = (new MemberUserMapper())->delete([
            [
                'column'   => 'id',
                'operator' => 'IN',
                'value'    => $inputs['callback'],
                'type'     => \PDO::PARAM_INT,
            ],
        ]);

        if ($output === true) {
            $output = new Output();
            $output->setSuccess(true);
            $output->setMessage('Member(s) deleted successfully');

            (new Response())->echoContent($output->toJSON());
        }
    }

    /**
     * Update a member info.
     */
    public function updateMember()
    {
        $inputs = (new Request('POST'))->getInputs();

        if (!empty($inputs['id'])) {
            $output = (new MemberUserMapper())->updateById($inputs['id'], $inputs);

            (new Response())->echoContent($output->toJSON());
        }
    }

    /**
     * View a member info.
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function viewMember()
    {
        $urlParameters = $this->getURLParameters();

        if (empty($urlParameters['id'])) {
            return false;
        }

        /**
         * Start getting all the groups (not assigned ones).
         */
        // get all the available groups
        $criteria = [
            [
                'column' => 'status',
                'value'  => 'active',
                'type'   => \PDO::PARAM_INT,
            ],
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
         * Finish getting all the groups (not assigned ones).
         */
        $output = (new MemberUserMapper())->getOneById($urlParameters['id']);

        $member = $output->getData();

        $statuses = (new MemberUserMapper())->getEnumValues('status');

        /**
         * hi to language.
         */
        $language = Registry::getLanguageClass();
        $requiredFields = $language->get('requiredFields');
        $passwordRequirements = $language->get('passwordRequirements');
        $usernameRequirements = $language->get('usernameRequirements');
        /**
         * bye to language.
         */
        $componentTemplate = new ComponentTemplate();
        $componentTemplate->setTemplatePath($this->getTemplatesPath().'memberView.php');

        $timeZoneList = (new DateTimeUtility())->getTimeZones();

        $componentTemplate->setData([
            'member'               => $member,
            'statuses'             => $statuses,
            'groups'               => $groupValues,
            'updateFormUrl'        => '/admin/update-member',
            'timeZoneList'         => $timeZoneList,
            'requiredFields'       => $requiredFields,
            'passwordRequirements' => $passwordRequirements,
            'usernameRequirements' => $usernameRequirements,
        ]);

        // create the page for view
        $page = new Page($this->getRouteInfo()->getAccessRole());
        $page->setTitle('Admin View Member');

        (new View(get_class($this)))->make(
            $page,
            [
                'member' => $componentTemplate,
            ],
            null,
            new FormHandler('View Member')
        );
    }

    /**
     * @throws \Exception
     *
     * @return Output
     */
    public function forgotPassword()
    {
        // get email
        $inputs = (new Request('POST'))->getInputs(['email']);
        $output = (new AdminUser())->forgotPassword($inputs['email']);

        (new Response())->echoContent($output->toJSON());
    }

    /**
     * Display forgot password form.
     *
     * @throws \Exception
     */
    public function forgotPasswordForm()
    {
        $page = new Page($this->getRouteInfo()->getAccessRole());
        $page->setTitle('Forgot your password');

        /**
         * hi to language.
         */
        $requiredFields = Registry::getLanguageClass()->get('requiredFields');
        /**
         * bye to language.
         */
        $componentTemplate = new ComponentTemplate();
        $componentTemplate->setTemplatePath($this->getTemplatesPath().'memberForgotPassword.php');
        $componentTemplate->setData([
            'formUrl'        => '/admin/forgot-password',
            'requiredFields' => $requiredFields,
        ]);

        (new View())->make(
            $page,
            [
                'forgotPassword' => $componentTemplate,
            ],
            null,
            new FormHandler('forgotPasswordForm')
        );
    }

    /**
     * Display reset password form.
     *
     * @throws \Exception
     */
    public function resetPasswordForm()
    {
        $inputs = $this->getURLParameters();
        $output = (new AdminUser())->checkTokenIsValidByEmail($inputs['email'], $inputs['token']);

        $page = new Page($this->getRouteInfo()->getAccessRole());
        $page->setTitle('Reset your password');

        $componentTemplate = new ComponentTemplate();
        $componentTemplate->setTemplatePath($this->getTemplatesPath().'memberResetPassword.php');
        $componentTemplate->setData([
            'tokenValid' => $output->getSuccess(),
            'email'      => $inputs['email'],
            'token'      => $inputs['token'],
            'formUrl'    => '/admin/reset-password',
        ]);

        (new View())->make(
            $page,
            [
                'passwordReset' => $componentTemplate,
            ],
            null,
            new FormHandler('Password Reset')
        );
    }

    /**
     * Reset a member's password.
     */
    public function resetPassword()
    {
        $inputs = (new Request('POST'))->getInputs(['email', 'resetPasswordToken', 'password', 'passwordConfirmation']);
        $output = (new AdminUser())->resetPassword(
            $inputs['email'],
            $inputs['resetPasswordToken'],
            $inputs['password'],
            $inputs['passwordConfirmation']
        );

        (new Response())->echoContent($output->toJSON());
    }

    /**
     * Display a profile (current user info) form.
     *
     * @throws \Exception
     */
    public function profileForm()
    {
        $currentUser = (new AdminUser())->getLoggedIn();

        /**
         * hi to language.
         */
        $language = Registry::getLanguageClass();
        $requiredFields = $language->get('requiredFields');
        $passwordRequirements = $language->get('passwordRequirements');
        $usernameRequirements = $language->get('usernameRequirements');
        /**
         * bye to language.
         */
        $componentTemplate = new ComponentTemplate();
        $componentTemplate->setTemplatePath($this->getTemplatesPath().'profile.php');

        $timeZoneList = (new DateTimeUtility())->getTimeZones();

        $componentTemplate->setData([
            'member'               => $currentUser,
            'updateFormUrl'        => '/admin/update-profile',
            'timeZoneList'         => $timeZoneList,
            'requiredFields'       => $requiredFields,
            'passwordRequirements' => $passwordRequirements,
            'usernameRequirements' => $usernameRequirements,
        ]);

        // create the page for view
        $page = new Page($this->getRouteInfo()->getAccessRole());
        $page->setTitle('Your Profile');

        (new View())->make(
            $page,
            [
                'member' => $componentTemplate,
            ],
            null,
            new FormHandler('Your Profile')
        );
    }

    /**
     * Update current user info.
     */
    public function updateProfile()
    {
        $currentUser = (new AdminUser())->getLoggedIn();

        $inputs = (new Request('POST'))->getInputs();

        if ($currentUser instanceof AdminUser) {
            $output = (new AdminUserMapper())->updateById($currentUser->getId(), $inputs);

            (new Response())->echoContent($output->toJSON());
        }
    }
}
