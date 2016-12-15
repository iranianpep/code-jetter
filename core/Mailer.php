<?php

namespace CodeJetter\core;

use CodeJetter\core\io\Input;
use CodeJetter\core\security\Validator;
use CodeJetter\core\security\ValidatorRule;
use PHPMailer;

/**
 * Class Mailer.
 */
class Mailer
{
    private $phpMailer;

    /**
     * Mailer constructor.
     */
    public function __construct()
    {
        // True means throwing error if there is any
        $mailer = new PHPMailer(true);

        // set default configs
        $config = Registry::getConfigClass();
        $defaultMailer = $config->get('defaultMailer');
        $mailers = $config->get('mailers');

        if (!isset($mailers[$defaultMailer])) {
            throw new \Exception('Specified default mailer does not exist in mailers list');
        }

        $mailerConfig = $mailers[$defaultMailer];

        foreach ($mailerConfig as $configKey => $configValue) {
            switch ($configKey) {
                case 'IsSMTP':
                    if ($configValue === true) {
                        $mailer->IsSMTP();
                    }
                    break;
                case 'isHTML':
                    $mailer->isHTML($configValue);
                    break;
                default:
                    $mailer->$configKey = $configValue;
                    break;
            }
        }

        $this->setPhpMailer($mailer);
    }

    /**
     * @param $to
     * @param $subject
     * @param $message
     *
     * @throws \CodeJetter\libs\PHPMailer\phpmailerException
     * @throws \Exception
     *
     * @return bool
     */
    public function send($to, $subject, $message)
    {
        if (empty($to)) {
            throw new \Exception('Recipient for sending email cannot be empty');
        }

        try {
            $mailer = $this->getPhpMailer();
            $mailer->Subject = $subject;

            if (is_array($to)) {
                foreach ($to as $recipient) {
                    if ($this->validateEmail($recipient) !== true) {
                        throw new \Exception("Email: '{$recipient}' is not valid");
                    }

                    $mailer->AddAddress($recipient);
                }
            } else {
                if ($this->validateEmail($to) !== true) {
                    throw new \Exception("Email: '{$to}' is not valid");
                }

                $mailer->AddAddress($to);
            }

            $mailer->Body = $message;

            // This is the body in plain text for non-HTML mail clients
            $mailer->AltBody = 'just a dummy ALT body';
            $mailer->Send();
            $mailer->SmtpClose();

            return empty($mailer->IsError()) ? true : false;
        } catch (phpmailerException $e) {
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        } catch (\Exception $e) {
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }

    /**
     * @return PHPMailer
     */
    public function getPhpMailer()
    {
        return $this->phpMailer;
    }

    /**
     * @param PHPMailer $phpMailer
     */
    public function setPhpMailer(PHPMailer $phpMailer)
    {
        $this->phpMailer = $phpMailer;
    }

    /**
     * @param $email
     *
     * @return bool
     */
    public function validateEmail($email)
    {
        $emailInput = new Input('email', [
            new ValidatorRule('required'),
            new ValidatorRule('email'),
        ]);

        $validator = new Validator([$emailInput], ['email' => $email]);
        $validatorOutput = $validator->validate();

        return $validatorOutput->getSuccess();
    }
}
