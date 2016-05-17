<?php


    namespace CodeJetter\tests;


    use CodeJetter\components\user\models\AdminUser;
    use CodeJetter\components\user\services\UserAuthentication;
    use CodeJetter\core\App;

    // this is to fix Cannot send session cookie - headers already sent
    @session_start();

    class UserAuthenticationTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @runInSeparateProcess
         */
        public function testLogin()
        {
            $app = App::getInstance();
            $app->init('dev');

            /**
             * Create a dummy user
             */
            $adminUser = new AdminUser();
            $adminUser->setId(1);
            $adminUser->setEmail('test@gmail.com');
            $adminUser->setPassword('$2y$10$bmK6RJGY6qYD84LdwXtlIOgDeOJes9WugMLbvFY5K43p/pFXMH7AS');
            $adminUser->setStatus('active');
            $adminUser->setLive(1);

            $userAuthentication = new UserAuthentication();

            $inputOutputs = [
                [
                    'input' => '324324342',
                    'output' => false
                ],
                [
                    'input' => ' 324324342',
                    'output' => false
                ],
                [
                    'input' => ' 324324342 ',
                    'output' => false
                ],
                [
                    'input' => '',
                    'output' => false
                ]
            ];

            foreach ($inputOutputs as $inputOutput) {
                $output = $userAuthentication->login($adminUser, $inputOutput['input']);
                $userAuthentication->removeLoggedInUserFromSession($adminUser);
                $this->assertEquals($inputOutput['output'], $output->getSuccess());
            }
        }

        /**
         * @runInSeparateProcess
         */
        public function testIsUserActive()
        {
            $userAuthentication = new UserAuthentication();
            /**
             * Create a dummy user
             */
            $adminUser = new AdminUser();
            $adminUser->setId(1);
            $adminUser->setEmail('test@gmail.com');
            $adminUser->setPassword('$2y$10$bmK6RJGY6qYD84LdwXtlIOgDeOJes9WugMLbvFY5K43p/pFXMH7AS');
            $adminUser->setStatus('active');
            $adminUser->setLive(1);

            $result = $userAuthentication->isUserActive($adminUser);
            $this->assertEquals(true, $result);

            $adminUser->setLive(null);

            $result = $userAuthentication->isUserActive($adminUser);
            $this->assertEquals(false, $result);

            $adminUser->setLive(1);

            $result = $userAuthentication->isUserActive($adminUser);
            $this->assertEquals(true, $result);

            $adminUser->setStatus('inactive');

            $result = $userAuthentication->isUserActive($adminUser);
            $this->assertEquals(false, $result);
        }

        /**
         * @runInSeparateProcess
         */
        public function testIsLoggedIn()
        {
            $app = App::getInstance();
            $app->init('dev');

            /**
             * Create a dummy user
             */
            $adminUser = new AdminUser();
            $adminUser->setId(1);
            $adminUser->setEmail('test@gmail.com');
            $adminUser->setPassword('$2y$10$bmK6RJGY6qYD84LdwXtlIOgDeOJes9WugMLbvFY5K43p/pFXMH7AS');
            $adminUser->setStatus('active');
            $adminUser->setLive(1);

            $userAuthentication = new UserAuthentication();

            $output = $userAuthentication->login($adminUser, '324324342');
            $this->assertEquals(false, $output->getSuccess());

//            $loggedInOutput = $userAuthentication->isLoggedIn($adminUser);
//            $this->assertEquals(true, $loggedInOutput);
//
//            $userAuthentication->removeLoggedInUserFromSession($adminUser);
//
//            $loggedInOutput = $userAuthentication->isLoggedIn($adminUser);
//            $this->assertEquals(false, $loggedInOutput);
        }
    }
