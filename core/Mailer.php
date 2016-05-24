<?php

namespace CodeJetter\core;

use CodeJetter\libs\PHPMailer\PHPMailer;
use CodeJetter\libs\PHPMailer\phpmailerException;

/**
 * Class Mailer
 * @package CodeJetter\core
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
     * @return bool
     * @throws \CodeJetter\libs\PHPMailer\phpmailerException
     * @throws \Exception
     */
    public function send($to, $subject, $message)
    {
        if (empty($to)) {
            throw new \Exception('Recipient for sending email cannot be empty');
        }

        // TODO $to validate email?

        try {
            $mailer = $this->getPhpMailer();
            $mailer->Subject = $subject;

            if (is_array($to)) {
                foreach ($to as $recipient) {
                    $mailer->AddAddress($recipient);
                }
            } else {
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
}
