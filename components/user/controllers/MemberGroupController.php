<?php

namespace CodeJetter\components\user\controllers;

use CodeJetter\components\page\models\Page;
use CodeJetter\components\user\mappers\GroupMemberUserXrefMapper;
use CodeJetter\components\user\mappers\MemberGroupMapper;
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
use CodeJetter\core\utility\MysqlUtility;
use CodeJetter\core\View;
use CodeJetter\libs\TableGenerator\HeadCell;

/**
 * Class MemberGroupController
 * @package CodeJetter\components\user\controllers
 */
class MemberGroupController extends BaseController
{
    /**
     * Add a group
     */
    public function addGroup()
    {
        $inputs = (new Request('POST'))->getInputs();

        $output = (new MemberGroupMapper())->add($inputs);

        (new Response())->setContent($output->toJSON())->echoContent();
    }

    /**
     * List groups
     *
     * @throws \Exception
     */
    public function listGroups()
    {
        // for pagination
        // for simple router url path is enough, for regex ones base path is needed
        $pager = new Pager($this->getURLParameters(), $this->getBasePath(), $this->getRouteInfo()->getUrl());

        $numberCell = new HeadCell('#');
        $numberCell->setSortable(false);
        $numberCell->setSearchable(false);

        $actionsCell = new HeadCell('Actions');
        $actionsCell->setSortable(false);
        $actionsCell->setSearchable(false);

        $listHeaders = [
            $numberCell,
            new HeadCell('Name'),
            new HeadCell('Status'),
            $actionsCell
        ];

        // get order and search query from the query string
        $listConfig = Registry::getConfigClass()->get('list');
        $request = new Request();
        $searchQuery = $request->getQueryStringVariables($listConfig['query']);
        $order = $request->getSortingFromQueryString();
        $criteria = (new MysqlUtility())->generateSearchCriteria($listHeaders);

        $output = (new MemberGroupMapper())->getAll(
            $criteria,
            [],
            $order,
            $pager->getStart(),
            $pager->getLimit(),
            true
        );

        /**
         * If the current page is not page 1, and there is no data,
         * set pager to page 1 and get data again with start 0
         */
        if (empty($output['result']) && $output['total'] > 0 && $pager->getCurrentPage() > 1) {
            $pager->setCurrentPage(1);
            $output = (new MemberGroupMapper())->getAll($criteria, [], $order, 0, $pager->getLimit(), true);
        }

        $pager->setTotal($output['total']);

        /**
         * hi to language
         */
        $language = Registry::getLanguageClass();
        $requiredFields = $language->get('requiredFields');
        $uniqueFields = $language->get('uniqueField', ['field' => 'Group name']);
        /**
         * bye to language
         */

        // create component
        $componentTemplate = new ComponentTemplate();
        $componentTemplate->setTemplatePath($this->getTemplatesPath() . 'memberGroupList.php');
        $componentTemplate->setPager($pager);
        $componentTemplate->setData([
            'listHeaders' => $listHeaders,
            'groups' => $output['result'],
            'searchQueryKey' => $listConfig['query'],
            'searchQuery' => $searchQuery,
            'requiredFields' => $requiredFields,
            'uniqueFields' => $uniqueFields
        ]);

        // create the page for view
        $page = new Page($this->getRouteInfo()->getAccessRole());
        $page->setTitle('Groups');
        $page->setIntro('Here you can manage the groups for members');
        //$page->setCategory('Group Management');

        (new View())->make(
            $page,
            [
                'groups' => $componentTemplate
            ],
            null,
            new FormHandler('List Groups')
        );
    }

    /**
     * Update a group info
     */
    public function updateGroup()
    {
        $inputs = (new Request('POST'))->getInputs();

        if (!empty($inputs['id'])) {
            $output = (new MemberGroupMapper())->updateById($inputs['id'], $inputs);
            (new Response())->echoContent($output->toJSON());
        }
    }

    /**
     * Safely delete a group
     *
     * @throws \Exception
     */
    public function safeDeleteGroup()
    {
        $inputs = (new Request('POST'))->getInputs();

        if (!empty($inputs['id'])) {
            $output = (new MemberGroupMapper())->safeDeleteOne([
                [
                    'column' => 'id',
                    'value' => $inputs['id'],
                    'type' => \PDO::PARAM_INT
                ]
            ]);

            if ($output > 0) {
                (new GroupMemberUserXrefMapper())->safeDelete([
                    [
                        'column' => 'groupId',
                        'value' => $inputs['id'],
                        'type' => \PDO::PARAM_INT
                    ]
                ]);

                $output = new Output();
                $output->setSuccess(true);
                $output->setMessage('Group safely deleted');

                (new Response())->echoContent($output->toJSON());
            }
        }
    }

    /**
     * Safely batch delete groups
     *
     * @throws \Exception
     */
    public function safeBatchDeleteGroup()
    {
        $inputs = (new Request('POST'))->getInputs();

        /**
         * Start validating
         */
        $output = new Output();
        if (empty($inputs['callback'])) {
            $output->setSuccess(false);
            $output->setMessage('Please select groups first');

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

        $output = (new MemberGroupMapper())->safeDelete([
            [
                'column' => 'id',
                'operator' => 'IN',
                'value' => $inputs['callback'],
                'type' => \PDO::PARAM_INT
            ]
        ]);

        if ($output instanceof Output and (int) $output->getData() > 0) {
            (new GroupMemberUserXrefMapper())->safeDelete([
                [
                    'column' => 'groupId',
                    'operator' => 'IN',
                    'value' => $inputs['callback'],
                    'type' => \PDO::PARAM_INT
                ]
            ]);

            $output = new Output();
            $output->setSuccess(true);
            $output->setMessage('Group safely deleted');

            (new Response())->echoContent($output->toJSON());
        }
    }

    /**
     * Delete a group
     *
     * @throws \Exception
     */
    public function deleteGroup()
    {
        $inputs = (new Request('POST'))->getInputs();

        if (!empty($inputs['id'])) {
            /**
             * Do not need to delete xref, since foreign key is set to CASCADE on delete
             */
            $output = (new MemberGroupMapper())->deleteOne([
                [
                    'column' => 'id',
                    'value' => $inputs['id'],
                    'type' => \PDO::PARAM_INT
                ]
            ]);

            if ($output == 'true') {
                $output = new Output();
                $output->setSuccess(true);
                $output->setMessage('Group deleted');

                (new Response())->echoContent($output->toJSON());
            }
        }
    }

    /**
     * Batch delete groups
     *
     * @throws \Exception
     */
    public function batchDeleteGroup()
    {
        $inputs = (new Request('POST'))->getInputs();

        /**
         * Start validating
         */
        $output = new Output();
        if (empty($inputs['callback'])) {
            $output->setSuccess(false);
            $output->setMessage('Please select groups first');

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

        $output = (new MemberGroupMapper())->delete([
            [
                'column' => 'id',
                'operator' => 'IN',
                'value' => $inputs['callback'],
                'type' => \PDO::PARAM_INT
            ]
        ]);

        if ($output == 'true') {
            $output = new Output();
            $output->setSuccess(true);
            $output->setMessage('Group(s) deleted');

            (new Response())->echoContent($output->toJSON());
        }
    }
}