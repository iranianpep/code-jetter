<?php

namespace CodeJetter;

use CodeJetter\core\BaseConfig;

class ConfigTemplate extends BaseConfig
{
    /**
     * To avoid duplicates, use const in this case
     */
    const SESSION_TIMEOUT = 3600;

    protected static $configs = [
        /******************************
         * common configs between dev and prod environments
         */
        'ROOT_NAMESPACE' => 'CodeJetter',
        'DS' => DIRECTORY_SEPARATOR,
        // this is added to each model
        'mapperSuffix' => 'Mapper',
        'defaultTimeZone' => 'Australia/Melbourne',
        'defaultLanguageCode' => 'AU',
        'defaultCity' => 'Melbourne',
        // must have a file in language folder e.g. en.json
        'defaultLanguage' => 'en',
        'defaultComponentConfigFile' => 'config.json',

        /**
         * security
         */
        // in seconds - this is mainly used for checking user last activity
        'sessionTimeout' => self::SESSION_TIMEOUT,
        // in seconds - this is mainly used for 'forgot password' functionality
        'tokenLifetime' => 3600,
        'defaultTokenHash' => 'sha1',
        'defaultCSRFHtmlTokenName' => 'csrfToken',
        'defaultGlobalCSRFHtmlTokenName' => 'globalCSRFToken',
        'maxFormTokens' => 10,
        // If regex is changed, you might need to change en.json file as well to reflect the new requirements
        'rulesConfigs' => [
            'id'       => [
                'regex' => '/\s/'
            ],
            'url'      => [
                'regex' => '_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,}))\.?)(?::\d{2,5})?(?:[/?#]\S*)?$_iuS'
            ],
            'username' => [
                // Cannot start with _, and must be between 3 to 20 characters
                'regex' => '/^[A-Za-z0-9]{1}+[A-Za-z0-9_]{2,19}$/'
            ],
            'password' => [
                // 6 to 20 characters - at least 1 lower case, 1 upper case, 1 number - can have space and special characters
                'regex' => '/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]{0,}).{6,20}$/'
            ],
        ],

        /**
         * request
         */
        'removeURLTrailingSlash' => true,

        /**
         * router
         */
        'defaultComponent' => 'page',
        'defaultController' => 'Page',
        'defaultAction' => 'index',
        'defaultRole' => 'public',
        'regexDelimiter' => '#',
        'regexTypedPatterns' => [
            'any' => '[^/]+',
            'int' => '[0-9]+',
            'string' => '[0-9a-zA-Z_-]+',
            'alpha' => '[a-zA-Z]+',
            'alphanumeric' => '[0-9a-zA-Z]+'
        ],

        /**
         * Authentication
         *
         * Role is determined based on baseURL if is specified. Otherwise accessRole for each route is considered
         * If accessRole is empty, defaultRole from config (this array) is considered
         */
        'roles' => [
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
        ],

        /**
         * redirections
         */
        'redirections' => [
            'MemberUser' => [
                'login' => '/login',
                'default' => '/account/members',
                'resetPassword' => '/reset-password'
            ],
            'AdminUser' => [
                'login' => '/admin/login',
                'default' => '/admin/members',
                'resetPassword' => '/admin/reset-password'
            ]
        ],

        /** SMS API */
        'currentSMSAPI' => 'TelstraAPI',
        'TelstraAPI' => [
            'getTokenURL' => 'https://api.telstra.com/v1/oauth/token',
            'sendSMSURL' => 'https://api.telstra.com/v1/sms/messages',
            'key' => 'API_KEY',
            'secret' => 'API_SECRET'
        ],

        /**
         * layout
         */
        'list' => [
            'pager' => [
                'limits' => [
                    5,
                    10,
                    20,
                    50,
                    100
                ],
                'defaultLimit' => 10
            ],
            'orderBy' => 'ob',
            'orderDir' => 'od',
            'query' => 'q'
        ],
        'defaultMasterTemplate' => 'default.php',
        'globalJSConfiguration' => [
            'sessionTimeout' => self::SESSION_TIMEOUT,
            // notification time BEFORE session timeout
            'notifySessionTimeout' => 60,
            // in seconds
            'sessionTimeoutCheckerInterval' => 1
        ],

        /**
         *  Page
         */
        // TODO can be moved to roles
        'accessRolesRobot' => [
            'public' => 'index, follow',
            'admin' => 'noindex, nofollow',
            'member' => 'noindex, nofollow',
            'guest' => 'index, follow'
        ],

        /**
         * Database
         */
        'mapperTableRelations' => [
            'MemberUserMapper' => 'member_users',
            'AdminUserMapper' => 'admin_users',
            'MemberGroupMapper' => 'member_groups',
            'GroupMemberUserXrefMapper' => 'group_member_user_xref'
        ],
        /**
         * Error handler & logger
         */
        'errorHandler' => [
            // If is set to false, does not do anything - This also applies to manual logging as well
            'inOperation' => true,
            // does NOT log if is true and ini_set('display_errors', 0) - does not apply to manual logging
            'respectDisplayErrors' => false,
            // does NOT log if is true and error_reporting(0) - does not apply to manual logging
            'respectErrorReporting' => false,
            'bypassInternalErrorHandler' => false,
            // Blacklist does not apply to manual logging e.g. (new CustomErrorHandler())->logError('this is a test');
            'blacklist' => [
                'inOperation' => false,
                'strings' => [
                    // example: 'Undefined variable'
                ],
                'regex' => [
                    // example: '/(Undefined variable)/'
                ],
                'components' => [
                ]
            ],
            'monolog' => [
                'channel' => 'logger',
                'handlers' => [
                    'file' => [
                        'active' => true,
                        // should be full path
                        'path' => '/Applications/MAMP/htdocs/CodeJetter/temp/custom_error_log.log'
                    ],
                    'hipchat' => [
                        'active' => false,
                        'token' => '',
                        // room name or room id
                        'room' => ''
                    ],
                    'mongo' => [
                        'active' => false,
                        'server' => '',
                        'db' => '',
                        'collection' => ''
                    ],
                    // For this one Chrome Logger extension needs to be installed to view errors in Chrome console
                    'chrome' => [
                        'active' => false,
                    ]
                ]
            ]
        ],

        /**
         * Email
         */
        'defaultMailer' => 'PHPMailer',
        'mailers' => [
            'PHPMailer' => [
                'IsSMTP' => true,
                'Host' => 'smtp.gmail.com',
                // 2 to enable SMTP debug information
                'SMTPDebug' => 0,
                'SMTPAuth' => true,
                'SMTPSecure' => 'tls',
                'Port' => 587,
                'Username' => 'GMAIL_EMAIL',
                /**
                 * For Gmail If you get the error: SMTP Error: Could not authenticate, check here:
                 * http://stackoverflow.com/questions/3949824/smtp-error-could-not-authenticate-in-phpmailer/37425237#37425237
                 *
                 * Turn off 2-step verification
                 * Enable less secure apps access
                 * Also try to change the password, even it does not have any special characters
                 */
                'Password' => 'GMAIL_EMAIL_PASSWORD',
                // Highest priority - Email priority (1 = High, 3 = Normal, 5 = low)
                'Priority' => 1,
                'CharSet' => 'UTF-8',
                'Encoding' => '8bit',
                'ContentType' => 'text/html; charset=utf-8\r\n',
                'From' => 'GMAIL_EMAIL',
                'FromName' => 'CodeJetter sender',
                // RFC 2822 Compliant for Max 998 characters per line
                'WordWrap' => 980,
                'isHTML' => true
            ]
        ],

        /******************************
         * dev and prod environments configs
         */

        'dev' => [
            /**
             * general
             */
            'URL' => 'http://localhost:8888',
            // put slash at the end of the url
            'URI' => '/Applications/MAMP/htdocs/CodeJetter/',
            'debug' => true,
            'debugTemplates' => false,

            /**
             * database
             */
            'defaultDB' => 'MySQL',
            'databases' => [
                'MySQL' => [
                    'host' => 'localhost',
                    'port' => '8889',
                    'user' => 'root',
                    'pass' => 'root',
                    'database' => 'DATABASE_NAME',
                    //'tableSuffix' => '_SUFFIX',
                    'tablePrefix' => 'cj_'
                ]
            ]
        ],
        'prod' => [
            /**
             * general
             */
            'URL' => 'http://your-site.com',
            // put slash at the end of the url
            'URI' => '/path/to/public/',
            'debug' => false,
            'debugTemplates' => false,

            /**
             * database
             */
            'defaultDB' => 'MySQL',
            'databases' => [
                'MySQL' => [
                    'host' => 'localhost',
                    'port' => '8889',
                    'user' => 'root',
                    'pass' => 'root',
                    'database' => 'DATABASE_NAME',
                    //'tableSuffix' => '_SUFFIX',
                    'tablePrefix' => 'cj_'
                ]
            ]
        ]
    ];
}
