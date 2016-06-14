<?php

namespace CodeJetter\components\contact\controllers;

use CodeJetter\components\contact\mappers\ContactMessageMapper;
use CodeJetter\components\page\models\Page;
use CodeJetter\core\BaseController;
use CodeJetter\core\FormHandler;
use CodeJetter\core\io\Request;
use CodeJetter\core\io\Response;
use CodeJetter\core\layout\blocks\ComponentTemplate;
use CodeJetter\core\Registry;
use CodeJetter\core\View;

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

        $output = (new ContactMessageMapper())->add($inputs);

        if ($output->getSuccess() === true) {
            /**
             * hi to language
             */
            $language = Registry::getLanguageClass();
            $successfulSubmission = $language->get('successfulContactMessageSubmission');
            /**
             * bye to language
             */

            $output->setMessage($successfulSubmission);
        }

        (new Response())->echoContent($output->toJSON());
    }
}
