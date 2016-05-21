<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 24/04/15
 * Time: 7:23 PM
 */

namespace CodeJetter\components\user\models;

use CodeJetter\components\user\services\UserAuthentication;
use CodeJetter\core\BaseModel;
use CodeJetter\core\io\Input;
use CodeJetter\core\io\Output;
use CodeJetter\core\Registry;
use CodeJetter\core\security\Validator;
use CodeJetter\core\security\ValidatorRule;
use CodeJetter\core\utility\StringUtility;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Class User
 * @package CodeJetter\components\user\models
 */
abstract class User extends BaseModel
{
    protected $name;
    protected $username;
    protected $email;
    protected $phone;
    protected $password;
    protected $status;
    protected $token;
    protected $tokenGeneratedAt;
    protected $timeZone;

    /**
     * Start setting & getting
     */

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return int
     */
    public function getTokenGeneratedAt()
    {
        return $this->tokenGeneratedAt;
    }

    /**
     * @param int $tokenGeneratedAt
     */
    public function setTokenGeneratedAt($tokenGeneratedAt)
    {
        $this->tokenGeneratedAt = $tokenGeneratedAt;
    }

    /**
     * @return string
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }

    /**
     * @param string $timeZone
     */
    public function setTimeZone($timeZone)
    {
        $this->timeZone = $timeZone;
    }

    /**
     * Finish setting & getting
     */

    /**
     * @return User|false
     */
    public function getLoggedIn()
    {
        $userModel = (new StringUtility())->getClassNameFromNamespace(get_class($this));
        $result = (new UserAuthentication())->getLoggedIn([$userModel]);

        return isset($result[$userModel]) ? $result[$userModel] : false;
    }

    /**
     * @param $username
     * @param $password
     *
     * @return bool|Output
     * @throws \Exception
     */
    public function login($username, $password)
    {
        // determine which mapper should be called
        $mapperName = $this->getMapperName();

        // get user by username
        $output = (new $mapperName())->getOneByUsername($username);

        if (!$output instanceof Output) {
            return false;
        }

        if ($output->getSuccess() !== true) {
            $output->setSuccess(false);
            $output->setMessage('Please try again');
            return $output;
        }

        $foundUser = $output->getData();

        $output = new Output();

        if (empty($foundUser) || !$foundUser instanceof User) {
            $output->setSuccess(false);
            $output->setMessage('Please try again');
            return $output;
        }

        return (new UserAuthentication())->login($foundUser, $password);
    }

    /**
     * @return Output
     * @throws \Exception
     */
    public function logout()
    {
        (new UserAuthentication())->logout($this);
    }

    /**
     * @param $email
     *
     * @return bool|Output
     * @throws \Exception
     */
    public function forgotPassword($email)
    {
        /**
         * Start validating email
         */
        $requiredRule = new ValidatorRule('required');
        $emailRule = new ValidatorRule('email');

        $emailInput = new Input('email', [$requiredRule, $emailRule]);
        $validatorOutput = (new Validator([$emailInput], ['email' => $email]))->validate();

        $output = new Output();
        if ($validatorOutput->getSuccess() !== true) {
            $output->setSuccess(false);
            $output->setMessages($validatorOutput->getMessages());
            return $output;
        }
        /**
         * Finish validating email & token
         */

        // verify user: check to see if the email exists in db and is active and parent id is 0
        $mapperName = $this->getMapperName();

        $mapperOutput = (new $mapperName())->getOneByEmail($email, null, 'active');

        if (!$mapperOutput instanceof Output) {
            return false;
        }

        if ($mapperOutput->getSuccess() !== true) {
            $output->setSuccess(false);
            $output->setMessage('Could not find any active user with this email');
            return $output;
        }

        // get user
        $user = $mapperOutput->getData();

        if (!$user instanceof User) {
            $output->setSuccess(false);
            $output->setMessage('Could not find any active user with this email');
            return $output;
        }

        return (new UserAuthentication())->forgetPassword($user);
    }

    /**
     * @param $email
     * @param $resetPasswordToken
     * @param $password
     * @param $passwordConfirmation
     *
     * @return Output
     * @throws \Exception
     */
    public function resetPassword($email, $resetPasswordToken, $password, $passwordConfirmation)
    {
        /**
         * Start validating email & token
         */
        $requiredRule = new ValidatorRule('required');
        $emailRule = new ValidatorRule('email');

        $emailInput = new Input('email', [$requiredRule, $emailRule]);

        $validatorOutput = (new Validator([$emailInput], ['email' => $email]))->validate();

        $output = new Output();
        if ($validatorOutput->getSuccess() !== true) {
            $output->setSuccess(false);
            $output->setMessages($validatorOutput->getMessages());
            return $output;
        }
        /**
         * Finish validating email & token
         */

        /**
         * Start verifying email & token combination
         */
        $mapperName = $this->getMapperName();
        $mapperOutput = (new $mapperName())->getOneByEmail($email, null, 'active');

        if (!$mapperOutput instanceof Output) {
            return false;
        }

        if ($mapperOutput->getSuccess() !== true) {
            $output->setSuccess(false);
            $output->setMessage('Email does not exist');
            return $output;
        }

        $user = $mapperOutput->getData();

        if (!$user instanceof User) {
            $output->setSuccess(false);
            $output->setMessage('Could not find any user with this email');
            return $output;
        }

        return (new UserAuthentication())->resetPassword($user, $resetPasswordToken, $password, $passwordConfirmation);
    }

    /**
     * @param $email
     * @param $token
     *
     * @return Output
     * @throws \Exception
     */
    public function checkTokenIsValidByEmail($email, $token)
    {
        $output = new Output();

        /**
         * Start validating email & token
         */
        $requiredRule = new ValidatorRule('required');
        $emailRule = new ValidatorRule('email');

        $emailInput = new Input('email', [$requiredRule, $emailRule]);
        $tokenInput = new Input('token', [$requiredRule]);

        $validatorOutput = (new Validator([
            $emailInput,
            $tokenInput
        ], ['email' => $email, 'token' => $token]))->validate();

        if (!$validatorOutput instanceof Output) {
            return false;
        }

        if ($validatorOutput->getSuccess() !== true) {
            $output->setSuccess(false);
            $output->setMessages($validatorOutput->getMessages());
            return $output;
        }
        /**
         * Finish validating email & token
         */

        /**
         * Start verifying email & token combination
         */
        $mapperName = $this->getMapperName();
        $mapperOutput = (new $mapperName())->getOneByEmail($email, null, 'active');

        if (!$mapperOutput instanceof Output) {
            return false;
        }

        if ($mapperOutput->getSuccess() !== true) {
            $output->setSuccess(false);
            $output->setMessage('Email does not exist');
            return $output;
        }

        $user = $mapperOutput->getData();

        if (!$user instanceof User) {
            $output->setSuccess(false);
            $output->setMessage('Could not find any user with this email');
            return $output;
        }

        if ($user->getToken() === $token) {
            // check token lifetime
            $tokenGeneratedAt = $user->getTokenGeneratedAt();
            $tokenLivedTime = time() - strtotime($tokenGeneratedAt);

            $tokenLifetime = Registry::getConfigClass()->get('tokenLifetime');
            $tokenExpired = $tokenLivedTime <= $tokenLifetime ? false : true;
        } else {
            $tokenExpired = true;
        }
        /**
         * Finish verifying email & token combination
         */

        if ($tokenExpired === false) {
            $output->setSuccess(true);
            $output->setData($user);
        } else {
            $output->setSuccess(false);
            $output->setMessage('Token is expired');
        }

        return $output;
    }
}
