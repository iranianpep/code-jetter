<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 24/02/16
 * Time: 8:51 AM.
 */

namespace CodeJetter\components\user\services;

use CodeJetter\components\user\models\User;
use CodeJetter\core\io\Input;
use CodeJetter\core\io\Output;
use CodeJetter\core\Mailer;
use CodeJetter\core\Registry;
use CodeJetter\core\RouteInfo;
use CodeJetter\core\security\Security;
use CodeJetter\core\security\Validator;
use CodeJetter\core\security\ValidatorRule;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Class UserAuthentication.
 */
class UserAuthentication
{
    /**
     * @param User $user
     * @param      $password
     *
     * @throws \Exception
     *
     * @return Output
     */
    public function login(User $user, $password)
    {
        $output = new Output();

        $userModel = $user->getClassNameFromNamespace();
        $redirections = Registry::getConfigClass()->get('redirections');

        if ($this->isUserActive($user) !== true) {
            $output->setSuccess(false);
            $output->setMessage('Please try again');

            return $output;
        }

        // check if the user is already logged in
        if ($this->isLoggedIn($user) === true) {
            $output->setSuccess(true);
            $output->setMessage('Logged in successfully');

            $output->setRedirectTo($redirections[$userModel]['default']);

            return $output;
        }

        // check pass
        if (password_verify($password, $user->getPassword()) === true) {
            // state is changed, so change the session id and delete the old one
            session_regenerate_id(true);

            $time = time();
            $_SESSION['loggedIn'][$userModel] = [
                'loggedAt'       => $time,
                'lastActivityAt' => $time,
                'userId'         => $user->getId(),
                'mapper'         => $user->getMapperName(true),
            ];

            $output->setSuccess(true);
            $output->setMessage('Logged in successfully');

            $output->setRedirectTo($redirections[$userModel]['default']);
        } else {
            $output->setSuccess(false);
            $output->setMessage('Please try again');
        }

        return $output;
    }

    /**
     * @param User $user
     * @param bool $updateLastActivityAt
     *
     * @return bool
     */
    public function isLoggedIn(User $user, $updateLastActivityAt = true)
    {
        $userModel = $user->getClassNameFromNamespace();

        $loggedInUsers = $this->getAllLoggedIn([$userModel]);

        // before updating $updateLastActivityAt make sure user is logged in
        if (!empty($loggedInUsers) && $updateLastActivityAt === true) {
            $this->updateLastActivityAt($user);
        }

        if (empty($loggedInUsers)) {
            // reset the user if for some reason (e.g. last activity) user is not logged
            $this->removeLoggedInUserFromSession($user);
        }

        return (empty($loggedInUsers)) ? false : true;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function updateLastActivityAt(User $user)
    {
        $userModel = $user->getClassNameFromNamespace();

        if (isset($_SESSION['loggedIn'][$userModel])) {
            $_SESSION['loggedIn'][$userModel]['lastActivityAt'] = time();

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array|null $whiteList
     *
     * @throws \Exception
     *
     * @return array|bool
     */
    public function getAllLoggedIn(array $whiteList = null)
    {
        if (!isset($_SESSION['loggedIn']) || empty($_SESSION['loggedIn']) || !is_array($_SESSION['loggedIn'])) {
            return false;
        }

        $loggedInUsers = [];
        foreach ($_SESSION['loggedIn'] as $userModel => $loggedIn) {
            if (empty($userModel) || !isset($loggedIn['mapper']) || !isset($loggedIn['userId'])) {
                continue;
            }

            // if $whiteList is set, filter the users based on the white list, otherwise get everything
            if ($whiteList !== null && !in_array($userModel, $whiteList)) {
                continue;
            }

            $output = new Output();
            if (class_exists($loggedIn['mapper'])) {
                $output = (new $loggedIn['mapper']())->getOneById($loggedIn['userId']);
            }

            if (!$output instanceof Output || $output->getSuccess() !== true) {
                continue;
            }

            $foundUser = $output->getData();

            if ($this->isUserActive($foundUser) !== true) {
                continue;
            }

            $sessionTimeout = Registry::getConfigClass()->get('sessionTimeout');

            if (empty($loggedIn['lastActivityAt'])) {
                // session for this user has expired
                continue;
            }

            $lastActivity = $loggedIn['lastActivityAt'];
            $currentTime = time();

            if ($currentTime - $lastActivity > $sessionTimeout) {
                // session for this user has expired
                continue;
            }

            $loggedInUsers[$userModel] = $foundUser;
        }

        return $loggedInUsers;
    }

    /**
     * Return the current access role.
     *
     * @return bool|string
     */
    public function viewingAs()
    {
        $routeInfo = Registry::getRouterClass()->getLastRoute();

        if (empty($routeInfo)) {
            return false;
        }

        return $routeInfo->getAccessRole();
    }

    /**
     * @return bool
     */
    public function viewingAsAdmin()
    {
        return ($this->viewingAs()) === 'admin' ? true : false;
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function getCurrentLoggedIn()
    {
        $routeInfo = Registry::getRouterClass()->getLastRoute();

        if (!$routeInfo instanceof RouteInfo) {
            return false;
        }

        if (empty($routeInfo->getAccessRole())) {
            return false;
        }

        $roles = Registry::getConfigClass()->get('roles');

        if (empty($roles[$routeInfo->getAccessRole()]) || empty($roles[$routeInfo->getAccessRole()]['user'])) {
            return false;
        }

        $loggedIn = $this->getAllLoggedIn([$roles[$routeInfo->getAccessRole()]['user']]);

        if (empty($loggedIn)) {
            return false;
        }

        return $loggedIn[$roles[$routeInfo->getAccessRole()]['user']];
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function removeLoggedInUserFromSession(User $user)
    {
        /**
         * model name is used to manage the case when an admin and a user logged in on the same browser
         * otherwise if one of the user logged out, the other one will be logged out as well.
         */
        $userModel = $user->getClassNameFromNamespace();

        if (isset($_SESSION['loggedIn'][$userModel])) {
            unset($_SESSION['loggedIn'][$userModel]);

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param User $user
     */
    public function logout(User $user)
    {
        if ($this->removeLoggedInUserFromSession($user) === true) {
            $userModel = $user->getClassNameFromNamespace();
            $redirections = Registry::getConfigClass()->get('redirections');
            header('Location: '.$redirections[$userModel]['login']);
        }

        exit;
    }

    /**
     * @param User $user
     * @param      $token
     * @param      $password
     * @param      $passwordConfirmation
     *
     * @throws \Exception
     *
     * @return Output
     */
    public function resetPassword(User $user, $token, $password, $passwordConfirmation)
    {
        $output = new Output();
        /**
         * Start validating passwords - email & resetPasswordToken are already validated.
         */
        $requiredRule = new ValidatorRule('required');
        $passwordRule = new ValidatorRule('password', ['confirmation' => $passwordConfirmation]);

        $passwordInput = new Input('password', [$requiredRule, $passwordRule]);

        $validatorOutput = (new Validator([$passwordInput], ['password' => $password]))->validate();

        if ($validatorOutput->getSuccess() !== true) {
            $output->setSuccess(false);
            $output->setMessages($validatorOutput->getMessages());

            return $output;
        }
        /*
         * Finish validating passwords
         */

        if ($this->isUserActive($user) !== true) {
            $output->setSuccess(false);
            $output->setMessage('User is not active');

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
        /*
         * Finish verifying email & token combination
         */

        if ($tokenExpired === true) {
            $output->setSuccess(false);
            $output->setMessage('Token is expired');

            return $output;
        }

        /**
         * Start resetting the password:.
         */
        // update user password with the new one
        $mapperName = $user->getMapperName(true);
        $output = (new $mapperName())->updateById($user->getId(), [
            'email'                => $user->getEmail(),
            'password'             => $password,
            'passwordConfirmation' => $passwordConfirmation,
        ]);

        if (!$output instanceof Output) {
            return false;
        }
        /**
         * Finish resetting the password:.
         */
        $finalOutput = new Output();
        if ($output->getSuccess() === true) {
            $finalOutput->setSuccess(true);
            $finalOutput->setMessage('Your password updated successfully');
        } else {
            $finalOutput->setSuccess(false);
            $finalOutput->setMessage($output->getMessage());
        }

        return $finalOutput;
    }

    /**
     * @param User $user
     *
     * @throws \Exception
     *
     * @return bool|Output
     */
    public function forgetPassword(User $user)
    {
        $output = new Output();

        if ($this->isUserActive($user) !== true) {
            $output->setSuccess(false);
            $output->setMessage('Could not find any active user with this email');

            return $output;
        }

        // generate token
        $token = (new Security())->generateToken();

        // store token & token generation time
        $toBeUpdated = [
            'email'            => $user->getEmail(),
            'token'            => $token,
            'tokenGeneratedAt' => time(),
        ];

        $mapperName = $user->getMapperName(true);
        $mapperOutput = (new $mapperName())->updateById($user->getId(), $toBeUpdated);

        if (!$mapperOutput instanceof Output) {
            return false;
        }

        // check if updated successfully
        $output = new Output();
        if ($mapperOutput->getSuccess() !== true) {
            $output->setSuccess(false);
            $output->setMessage('Could not update the user details');

            return $output;
        }

        // all good, send the email including email and token
        $baseURL = Registry::getConfigClass()->get('URL');

        $userModel = $user->getClassNameFromNamespace();
        $redirections = Registry::getConfigClass()->get('redirections');

        if (!isset($redirections[$userModel])) {
            return false;
        }

        $resetPasswordLink = $baseURL.$redirections[$userModel]['resetPassword'].'/email/'
            .$user->getEmail().'/token/'.$token;

        $email = $user->getEmail();
        $mailer = new Mailer();

        $result = $mailer->send($email, 'Password Reset', $resetPasswordLink);

        if ($result !== true) {
            $output->setMessage("There was an issue in sending an email to {$email}");
            $output->setSuccess(false);
        } else {
            $output->setMessage("An email including a link to reset your password is sent to {$email}");
            $output->setSuccess(true);
        }

        return $output;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function isPrivateDestinationAccessible(User $user)
    {
        return $this->isLoggedIn($user) !== true ? false : true;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function isGuestDestinationAccessible(User $user)
    {
        return $this->isLoggedIn($user) === true ? false : true;
    }

    /**
     * Check if the user is active AND is NOT archived.
     *
     * @param $user
     *
     * @return bool
     */
    public function isUserActive($user)
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($user->getStatus() !== 'active') {
            return false;
        }

        if ((int) $user->getLive() !== 1) {
            return false;
        }

        if (!empty($user->getArchivedAt())) {
            return false;
        }

        return true;
    }
}
