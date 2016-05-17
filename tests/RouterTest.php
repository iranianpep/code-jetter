<?php

    namespace CodeJetter\tests;

    use CodeJetter\core\App;
    use CodeJetter\core\Router;
    use ReflectionClass;

    // this is to fix Cannot send session cookie - headers already sent
    @session_start();

class RouterTest extends \PHPUnit_Framework_TestCase {
    public function testRegexMatch()
    {
        $app = App::getInstance();
        $app->init('dev');

        $router = new Router();

        $inputOutputs = [
            [
                'url' => '/account/members/page/1/limit/13',
                'pattern' => '/account/members/page/{page:int}/limit/{limit:int:?}',
                'match' => [
                    0 => '/account/members/page/1/limit/13',
                    'page' => '1',
                    1 => '1',
                    'limit' => '13',
                    2 => '13'
                ]
            ],
            [
                'url' => '/account/members/page/1/limit',
                'pattern' => '/account/members/page/{page:int}/limit/{limit:int:?}',
                'match' => [
                    0 => '/account/members/page/1/limit',
                    'page' => '1',
                    1 => '1'
                ]
            ],
            [
                'url' => '/account/members/page/1/',
                'pattern' => '/account/members/page/{page:int}/limit/{limit:int:?}',
                'match' => false
            ],
            [
                'url' => '/account/members/page/1',
                'pattern' => '/account/members/page/{page:int}/limit/{limit:int:?}',
                'match' => false
            ],
            [
                'url' => '/account/members/page/1/limit',
                'pattern' => '/account/members/page/{page:int}/limit/{limit:int:?}',
                'match' => [
                    0 => '/account/members/page/1/limit',
                    'page' => '1',
                    1 => '1'
                ]
            ],
            [
                'url' => '/account/members/page//limit/13',
                'pattern' => '/account/members/page/{page:int}/limit/{limit:int:?}',
                'match' => false
            ],
            [
                'url' => '/account/members',
                'pattern' => '/account/members/page/{page:int}/limit/{limit:int:?}',
                'match' => false
            ],
            [
                'url' => '/reset-password',
                'pattern' => '/reset-password/email/{email:any}/token/{token:any}',
                'match' => false
            ],
            [
                'url' => '/reset-password/',
                'pattern' => '/reset-password/email/{email:any}/token/{token:any}',
                'match' => false
            ],
            [
                'url' => '/reset-password/email',
                'pattern' => '/reset-password/email/{email:any}/token/{token:any}',
                'match' => false
            ],
            [
                'url' => '/reset-password/email/',
                'pattern' => '/reset-password/email/{email:any}/token/{token:any}',
                'match' => false
            ],
            [
                'url' => '/reset-password/email/test@test.com',
                'pattern' => '/reset-password/email/{email:any}/token/{token:any}',
                'match' => false
            ],
            [
                'url' => '/reset-password/email/test@test.com/',
                'pattern' => '/reset-password/email/{email:any}/token/{token:any}',
                'match' => false
            ],
            [
                'url' => '/reset-password/email/test@test.com/token',
                'pattern' => '/reset-password/email/{email:any}/token/{token:any}',
                'match' => false
            ],
            [
                'url' => '/reset-password/email/test@test.com/token/',
                'pattern' => '/reset-password/email/{email:any}/token/{token:any}',
                'match' => false
            ],
            [
                'url' => '/reset-password/email/test@test.com/token/123',
                'pattern' => '/reset-password/email/{email:any}/token/{token:any}',
                'match' => [
                    0 => '/reset-password/email/test@test.com/token/123',
                    'email' => 'test@test.com',
                    1 => 'test@test.com',
                    'token' => '123',
                    2 => '123'
                ]
            ],
            [
                'url' => '/reset-password/email/test@test.com',
                'pattern' => '/reset-password/email/{email:any}',
                'match' => [
                    0 => '/reset-password/email/test@test.com',
                    'email' => 'test@test.com',
                    1 => 'test@test.com'
                ]
            ],
            [
                'url' => '/reset-password/email',
                'pattern' => '/reset-password/email',
                'match' => [
                    0 => '/reset-password/email'
                ]
            ],
            [
                'url' => '/account/1',
                'pattern' => '/account/{id:any}',
                'match' => [
                    0 => '/account/1',
                    'id' => '1',
                    1 => 1
                ]
            ],
        ];

        foreach ($inputOutputs as $inputOutput) {
            $pattern = $inputOutput['pattern'];
            $path = $inputOutput['url'];

            $this->assertEquals($inputOutput['match'], $router->regexMatch($pattern, $path));
        }
    }

    public function testGetAccessRole()
    {
        $app = App::getInstance();
        $app->init('dev');

        $router = new Router();

        $roles1 = [
            'member' => [
                'user' => 'MemberUser',
                'destination' => 'private',
                'baseURL' => '/account/'
            ],
            'admin' => [
                'user' => 'AdminUser',
                'destination' => 'private',
                'baseURL' => '/admin/'
            ],
            'guest' => [
                'destination' => 'guest'
            ]
        ];

        $roles2 = [
            'member' => [
                'user' => 'MemberUser',
                'destination' => 'private',
                'baseURL' => '/account/admin/'
            ],
            'admin' => [
                'user' => 'AdminUser',
                'destination' => 'private',
                'baseURL' => '/account/'
            ],
            'guest' => [
                'destination' => 'guest'
            ]
        ];

        $roles3 = [
            'member' => [
                'user' => 'MemberUser',
                'destination' => 'private',
                'baseURL' => '/account/'
            ],
            'admin' => [
                'user' => 'AdminUser',
                'destination' => 'private',
                'baseURL' => '/account/admin/'
            ],
            'guest' => [
                'destination' => 'guest'
            ]
        ];

        $inputOutputs = [
            [
                'roles' => $roles1,
                'url' => '/account/members',
                'output' => 'member'
            ],
            [
                'roles' => $roles1,
                'url' => '/admin/members',
                'output' => 'admin'
            ],
            [
                'roles' => $roles1,
                'url' => '/admin/account/',
                'output' => 'admin'
            ],
            [
                'roles' => $roles1,
                'url' => '/account/admin/',
                'output' => 'member'
            ],
            [
                'roles' => $roles1,
                'url' => 'account/members',
                'output' => 'public'
            ],
            //////////
            [
                'roles' => $roles2,
                'url' => '/account/members',
                'output' => 'admin'
            ],
            [
                'roles' => $roles2,
                'url' => '/admin/members',
                'output' => 'public'
            ],
            [
                'roles' => $roles2,
                'url' => '/admin/account/',
                'output' => 'public'
            ],
            [
                'roles' => $roles2,
                'url' => 'account/members',
                'output' => 'public'
            ],
            [
                'roles' => $roles2,
                'url' => '/account/admin/',
                'output' => 'member'
            ],
            //////////
            [
                'roles' => $roles3,
                'url' => '/account/members',
                'output' => 'member'
            ],
            [
                'roles' => $roles3,
                'url' => '/admin/members',
                'output' => 'public'
            ],
            [
                'roles' => $roles3,
                'url' => '/admin/account/',
                'output' => 'public'
            ],
            [
                'roles' => $roles3,
                'url' => 'account/members',
                'output' => 'public'
            ],
            [
                'roles' => $roles3,
                'url' => '/account/admin/',
                'output' => 'admin'
            ],
        ];

        $getAccessRoleFunction = self::getMethod('getAccessRole');

        foreach ($inputOutputs as $inputOutput) {
            $result = $getAccessRoleFunction->invokeArgs($router, [$inputOutput['url'], $inputOutput['roles']]);
            $this->assertEquals($inputOutput['output'], $result);
        }
    }

    /**
     * To test a private method:
     *
     * @param $name
     *
     * @return \ReflectionMethod
     */
    protected static function getMethod($name) {
        $class = new ReflectionClass('CodeJetter\core\Router');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}
 