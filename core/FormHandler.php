<?php

namespace CodeJetter\core;

use CodeJetter\core\IO\Output;
use CodeJetter\core\security\Security;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Class FormHandler
 * @package CodeJetter\core
 */
class FormHandler
{
    private $formName;

    /**
     * @param $formName
     */
    public function __construct($formName)
    {
        $this->setFormName($formName);
    }

    /**
     * @param null $hashAlgorithm
     *
     * @return string
     * @throws \Exception
     */
    public function setAntiCSRF($hashAlgorithm = null)
    {
        /**
         * start getting a new token
         */
        $security = new Security();

        if ($hashAlgorithm !== null) {
            $security->setHashAlgorithm($hashAlgorithm);
        }
        /**
         * finish getting a new token
         */

        // clean the session to avoid DoS attack
        if (isset($_SESSION['forms'][$this->getFormName()])
            && count($_SESSION['forms'][$this->getFormName()]) >= $this->getMaxFormTokens()) {
            unset($_SESSION['forms'][$this->getFormName()]);
        }

        // set into the session
        $token = $security->generateToken();
        return $_SESSION['forms'][$this->getFormName()][$token] = $token;
    }

    /**
     * @param null   $hashAlgorithm
     * Does not use for the global one currently
     * @param null   $name
     * @param null   $id
     * @param string $mode
     * Values are global, perForm
     *
     * @return string
     * @throws \Exception
     */
    public function generateAntiCSRFHtml($hashAlgorithm = null, $name = null, $id = null, $mode = 'global')
    {
        // generate id html
        $id = !empty($id) ? "id='{$id}'" : '';

        switch ($mode) {
            case 'global':
                $tokenName = Registry::getConfigClass()->get('defaultGlobalCSRFHtmlTokenName');
                $name = !empty($name) ? $name : $tokenName;

                $app = App::getInstance();
                $tokenValue = $app->getAntiCSRFToken();

                $html = "<input type='hidden' name='{$name}' {$id} value='{$tokenValue}'>";
                break;
            case 'perForm':
                $tokenName = Registry::getConfigClass()->get('defaultCSRFHtmlTokenName');
                $name = !empty($name) ? $name : $tokenName;

                $html = "<input type='hidden' name='{$name}' {$id} value='{$this->setAntiCSRF($hashAlgorithm)}'>";
                break;
            default:
                throw new \Exception('Mode is not valid');
                break;
        }

        return $html;
    }

    /**
     * @param      $token
     * @param bool $resetAfterChecking
     *
     * @return Output
     * @throws \Exception
     */
    public function checkAntiCSRF($token, $resetAfterChecking = false)
    {
        $output = new Output();

        if (!empty($token)
            && isset($_SESSION['forms'][$this->getFormName()])
            && array_key_exists($token, $_SESSION['forms'][$this->getFormName()])) {
            $output->setSuccess(true);

            // reset token after checking if $resetAfterChecking is set to true
            if ($resetAfterChecking === true) {
                // remove this token and generate a new one
                unset($_SESSION['forms'][$this->getFormName()][$token]);
                $output->setData($this->setAntiCSRF());
            }
        } else {
            $output->setSuccess(false);
        }

        return $output;
    }

    /**
     * @return string
     */
    public function getFormName()
    {
        return $this->formName;
    }

    /**
     * @param string $formName
     */
    public function setFormName($formName)
    {
        $this->formName = $formName;
    }

    /**
     * @return int
     */
    public function getMaxFormTokens()
    {
        if (!isset($this->maxFormTokens)) {
            $this->setMaxFormTokens(Registry::getConfigClass()->get('maxFormTokens'));
        }

        return $this->maxFormTokens;
    }

    /**
     * @param int $maxFormTokens
     */
    public function setMaxFormTokens($maxFormTokens)
    {
        $this->maxFormTokens = $maxFormTokens;
    }
}
