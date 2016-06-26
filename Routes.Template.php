<?php

namespace CodeJetter;

class RoutesTemplate
{
    /**
     * Only last variable in regex can be optional
     *
     * @var array
     */
    public $routes = [
        /**
         * Simple
         */
        'simple' => [
            'GET' => [
                '/' => [
                    'component' => 'page',
                    'controller' => 'Page'
                ],
                '/contact' => [
                    'component' => 'contact',
                    'controller' => 'Contact'
                ],
                '/register' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'registerForm',
                    'accessRole' => 'guest'
                ],
                '/login' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'loginForm',
                    'accessRole' => 'guest'
                ],
                '/admin/login' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'loginForm',
                    'accessRole' => 'guest'
                ],
                '/admin/members' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'listMembers'
                ],
                '/admin/groups' => [
                    'component' => 'user',
                    'controller' => 'MemberGroup',
                    'action' => 'listGroups'
                ],
                '/admin/contact/messages' => [
                    'component' => 'contact',
                    'controller' => 'Contact',
                    'action' => 'listMessages'
                ],
                '/admin/profile' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'profileForm'
                ],
                '/account/profile' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'profileForm'
                ],
                '/logout' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'logout'
                ],
                '/admin/logout' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'logout'
                ],
                '/account/members' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'listUsers'
                ],
                '/reset-password' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'resetPasswordForm',
                    'accessRole' => 'guest'
                ],
                '/forgot-password' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'forgotPasswordForm',
                    'accessRole' => 'guest'
                ],
                '/admin/forgot-password' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'forgotPasswordForm',
                    'accessRole' => 'guest'
                ],
            ],
            'POST' => [
                '/register' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'register',
                    'accessRole' => 'guest'
                ],
                '/contact/new' => [
                    'component' => 'contact',
                    'controller' => 'Contact',
                    'action' => 'newMessage'
                ],
                '/login' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'login',
                    'accessRole' => 'guest'
                ],
                '/admin/login' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'login',
                    'accessRole' => 'guest'
                ],
                '/admin/add-member' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'addMember'
                ],
                '/admin/add-group-member' => [
                    'component' => 'user',
                    'controller' => 'MemberGroup',
                    'action' => 'addGroup'
                ],
                '/admin/update-group-member' => [
                    'component' => 'user',
                    'controller' => 'MemberGroup',
                    'action' => 'updateGroup'
                ],
                '/admin/safe-delete-group-member' => [
                    'component' => 'user',
                    'controller' => 'MemberGroup',
                    'action' => 'safeDeleteGroup'
                ],
                '/admin/safe-batch-delete-group-member' => [
                    'component' => 'user',
                    'controller' => 'MemberGroup',
                    'action' => 'safeBatchDeleteGroup'
                ],
                '/admin/delete-group-member' => [
                    'component' => 'user',
                    'controller' => 'MemberGroup',
                    'action' => 'deleteGroup'
                ],
                '/admin/batch-delete-group-member' => [
                    'component' => 'user',
                    'controller' => 'MemberGroup',
                    'action' => 'batchDeleteGroup'
                ],
                '/admin/contact/safe-delete-message' => [
                    'component' => 'contact',
                    'controller' => 'Contact',
                    'action' => 'safeDeleteMessage'
                ],
                '/admin/contact/safe-batch-delete-message' => [
                    'component' => 'contact',
                    'controller' => 'Contact',
                    'action' => 'safeBatchDeleteMessage'
                ],
                '/admin/contact/delete-message' => [
                    'component' => 'contact',
                    'controller' => 'Contact',
                    'action' => 'deleteMessage'
                ],
                '/admin/contact/batch-delete-message' => [
                    'component' => 'contact',
                    'controller' => 'Contact',
                    'action' => 'batchDeleteMessage'
                ],
                '/account/notify' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'notify'
                ],
                '/account/delete-child' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'safeDeleteChild'
                ],
                '/account/batch-delete-child' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'safeBatchDeleteChild'
                ],
                '/admin/delete-member' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'deleteMember'
                ],
                '/admin/batch-delete-member' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'batchDeleteMember'
                ],
                '/admin/safe-delete-member' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'safeDeleteMember'
                ],
                '/admin/safe-batch-delete-member' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'safeBatchDeleteMember'
                ],
                '/account/update-child' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'updateChild'
                ],
                '/admin/update-member' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'updateMember'
                ],
                '/account/add-child' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'addChild'
                ],
                '/reset-password' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'resetPassword',
                    'accessRole' => 'guest'
                ],
                '/admin/reset-password' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'resetPassword',
                    'accessRole' => 'guest'
                ],
                '/forgot-password' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'forgotPassword',
                    'accessRole' => 'guest'
                ],
                '/admin/forgot-password' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'forgotPassword',
                    'accessRole' => 'guest'
                ],
                '/account/update-profile' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'updateProfile'
                ],
                '/admin/update-profile' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'updateProfile'
                ]
            ]
        ],
        /**
         * Regex
         */
        'regex' => [
            'GET' => [
                '/account/members/page/{page:int}/limit/{limit:int:?}' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'listUsers',
                    'base' => '/account/members'
                ],
                '/admin/members/page/{page:int}/limit/{limit:int:?}' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'listMembers',
                    'base' => '/admin/members'
                ],
                '/admin/groups/page/{page:int}/limit/{limit:int:?}' => [
                    'component' => 'user',
                    'controller' => 'MemberGroup',
                    'action' => 'listGroups',
                    'base' => '/admin/groups'
                ],
                '/admin/contact/messages/page/{page:int}/limit/{limit:int:?}' => [
                    'component' => 'contact',
                    'controller' => 'Contact',
                    'action' => 'listMessages',
                    'base' => '/admin/contact/messages'
                ],
                // TODO think about type of parameter: currently is any
                '/reset-password/email/{email:any}/token/{token:any}' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'resetPasswordForm',
                    'base' => '/reset-password',
                    'accessRole' => 'guest'
                ],
                '/admin/reset-password/email/{email:any}/token/{token:any}' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'resetPasswordForm',
                    'base' => '/admin/reset-password',
                    'accessRole' => 'guest'
                ],
                '/admin/member/{id:int}' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'viewMember'
                ],
                '/account/member/{id:int}' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'viewChild'
                ]
            ],
            'POST' => [
//                '/reset-password/email/{email:any}/token/{token:any}' => [
//                    'component' => 'user',
//                    'controller' => 'MemberUser',
//                    'action' => 'resetPassword',
//                    'base' => '/reset-password',
//                    'accessRole' => 'guest'
//                ],
            ]
        ]
    ];
}
