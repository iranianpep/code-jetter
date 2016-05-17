<?php

namespace CodeJetter\core\security;

use CodeJetter\core\App;
use CodeJetter\core\Registry;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Class Security
 * @package CodeJetter\core\security
 */
class Security
{
    /**
     * @var string - This is different from $passwordHashAlgorithm
     */
    private $hashAlgorithm;

    /**
     * Security constructor.
     *
     * @param null $hashAlgorithm
     */
    public function __construct($hashAlgorithm = null)
    {
        if ($hashAlgorithm === null) {
            // get the default hash
            $hashAlgorithm = Registry::getConfigClass()->get('defaultTokenHash');
        }

        $this->setHashAlgorithm($hashAlgorithm);
    }

    /**
     * generate a token
     *
     * @return string
     */
    public function generateToken()
    {
        $hashAlgorithm = $this->getHashAlgorithm();

        if (isset($hashAlgorithm)) {
            return hash($hashAlgorithm, uniqid(mt_rand(), true));
        } else {
            return uniqid(mt_rand(), true);
        }
    }

    /**
     * @return string
     */
    public function getHashAlgorithm()
    {
        return $this->hashAlgorithm;
    }

    /**
     * @param $hashAlgorithm
     * @throws \Exception
     */
    public function setHashAlgorithm($hashAlgorithm)
    {
        /**
         * check if hashing algorithm is valid
         */
        if (!in_array($hashAlgorithm, hash_algos(), true)) {
            throw new \Exception('Hash algorithm is not valid');
        }

        $this->hashAlgorithm = $hashAlgorithm;
    }

    /**
     * @param      $plainPassword
     * @param null $passwordAlgorithmConstant
     *
     * @return bool|string
     * @throws \Exception
     */
    public function hashPassword($plainPassword, $passwordAlgorithmConstant = null)
    {
        if ($passwordAlgorithmConstant === null) {
            $passwordAlgorithmConstant = PASSWORD_DEFAULT;
        } else {
            // if the algorithm is provided check it against whitelist
            $whitelist = [PASSWORD_DEFAULT, PASSWORD_BCRYPT];

            if (!in_array($passwordAlgorithmConstant, $whitelist)) {
                throw new \Exception("Password algorithm constant: {$passwordAlgorithmConstant} is not valid");
            }
        }

        return password_hash($plainPassword, $passwordAlgorithmConstant);
    }

    /**
     * @param $token
     *
     * @return bool
     */
    public function checkAntiCSRF($token)
    {
        $app = App::getInstance();
        $antiCSRFToken = $app->getAntiCSRFToken();

        if ($antiCSRFToken === $token) {
            return true;
        } else {
            return false;
        }
    }
}
