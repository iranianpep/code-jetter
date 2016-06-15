<?php

namespace CodeJetter\components\contact\controllers;

use CodeJetter\components\contact\mappers\ContactMessageMapper;
use CodeJetter\components\page\models\Page;
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
 * Class ContactController
 * @package CodeJetter\components\contact\controllers
 */
class ContactController extends BaseController
{
    /**
     * @throws \Exception
     */
    public function index()
    {
        $page = new Page($this->getRouteInfo()->getAccessRole());
        $page->setTitle('Contact');

        /**
         * hi to language
         */
        $language = Registry::getLanguageClass();
        $requiredFields = $language->get('requiredFields');
        /**
         * bye to language
         */

        $componentTemplate = new ComponentTemplate();
        $componentTemplate->setTemplatePath('/components/contact/templates/contactForm.php');
        $componentTemplate->setData([
            'requiredFields' => $requiredFields
        ]);

        (new View())->make(
            $page,
            [
                'contact' => $componentTemplate
            ],
            null,
            new FormHandler('Contact')
        );
    }

    public function newMessage()
    {
        $inputs = (new Request('POST'))->getInputs();

        $addOutput = (new ContactMessageMapper())->add($inputs);

        $output = new Output();
        if ($addOutput->getSuccess() === true) {
            /**
             * hi to language
             */
            $language = Registry::getLanguageClass();
            $successfulSubmission = $language->get('successfulContactMessageSubmission');
            /**
             * bye to language
             */

            // do not expose anything to world, that's why another output is created
            $output->setSuccess(true);
            $output->setMessage($successfulSubmission);
        }

        (new Response())->echoContent($output->toJSON());
    }

    public function listMessages()
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
            new HeadCell('Email'),
            new HeadCell('Message'),
            $actionsCell
        ];

        // get order and search query from the query string
        $listConfig = Registry::getConfigClass()->get('list');
        $request = new Request();
        $searchQuery = $request->getQueryStringVariables($listConfig['query']);
        $order = $request->getSortingFromQueryString();
        $criteria = (new MysqlUtility())->generateSearchCriteria($listHeaders);

        $output = (new ContactMessageMapper())->getAll(
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
            $output = (new ContactMessageMapper())->getAll($criteria, [], $order, 0, $pager->getLimit(), true);
        }

        $pager->setTotal($output['total']);

        // create component template
        $componentTemplate = new ComponentTemplate();
        $componentTemplate->setTemplatePath('components/contact/templates/contactMessageList.php');
        $componentTemplate->setPager($pager);
        $componentTemplate->setData([
            'listHeaders' => $listHeaders,
            'messages' => $output['result'],
            'searchQueryKey' => $listConfig['query'],
            'searchQuery' => $searchQuery
        ]);

        // create the page for view
        $page = new Page($this->getRouteInfo()->getAccessRole());
        $page->setTitle('Contact Message List');

        (new View())->make(
            $page,
            [
                'messages' => $componentTemplate
            ],
            null,
            new FormHandler('List Messages')
        );
    }

    /**
     * Safely delete a message
     *
     * @throws \Exception
     */
    public function safeDeleteMessage()
    {
        $inputs = (new Request('POST'))->getInputs();

        if (!empty($inputs['id'])) {
            $output = (new ContactMessageMapper())->safeDeleteOne([
                [
                    'column' => 'id',
                    'value' => $inputs['id'],
                    'type' => \PDO::PARAM_INT
                ]
            ]);

            if ($output > 0) {
                $output = new Output();
                $output->setSuccess(true);
                $output->setMessage('Message safely deleted');

                (new Response())->echoContent($output->toJSON());
            }
        }
    }

    /**
     * Safely batch delete messages
     *
     * @throws \Exception
     */
    public function safeBatchDeleteMessage()
    {
        $inputs = (new Request('POST'))->getInputs();

        /**
         * Start validating
         */
        $output = new Output();
        if (empty($inputs['callback'])) {
            $output->setSuccess(false);
            $output->setMessage('Please select messages first');

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

        $output = (new ContactMessageMapper())->safeDelete([
            [
                'column' => 'id',
                'operator' => 'IN',
                'value' => $inputs['callback'],
                'type' => \PDO::PARAM_INT
            ]
        ]);

        if ($output > 0) {
            $output = new Output();
            $output->setSuccess(true);
            $output->setMessage('Message safely deleted');

            (new Response())->echoContent($output->toJSON());
        }
    }

    /**
     * Delete a message
     *
     * @throws \Exception
     */
    public function deleteMessage()
    {
        $inputs = (new Request('POST'))->getInputs();

        if (!empty($inputs['id'])) {
            /**
             * Do not need to delete xref, since foreign key is set to CASCADE on delete
             */
            $output = (new ContactMessageMapper())->deleteOne([
                [
                    'column' => 'id',
                    'value' => $inputs['id'],
                    'type' => \PDO::PARAM_INT
                ]
            ]);

            if ($output == 'true') {
                $output = new Output();
                $output->setSuccess(true);
                $output->setMessage('Message deleted');

                (new Response())->echoContent($output->toJSON());
            }
        }
    }

    /**
     * Batch delete messages
     *
     * @throws \Exception
     */
    public function batchDeleteMessage()
    {
        $inputs = (new Request('POST'))->getInputs();

        /**
         * Start validating
         */
        $output = new Output();
        if (empty($inputs['callback'])) {
            $output->setSuccess(false);
            $output->setMessage('Please select messages first');

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

        $output = (new ContactMessageMapper())->delete([
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
            $output->setMessage('Message(s) deleted');

            (new Response())->echoContent($output->toJSON());
        }
    }
}
