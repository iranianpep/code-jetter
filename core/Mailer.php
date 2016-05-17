<?php

namespace CodeJetter\core;

use CodeJetter\libs\PHPMailer\PHPMailer;

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
        $mailer = new PHPMailer();

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

        // TODO what is alt body?
        $mailer->AltBody = 'just a dummy ALT body';
        $mailer->Send();
        $mailer->SmtpClose();

        return $mailer->IsError();
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
