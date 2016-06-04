# Routing
- [Basic Routing](#basic)
- [Passing Parameters](#parameters)

<a name="basic"></a>
## Basic Routing
In Code Jetter all the routes are located in `core/Router.php` and that is probably the first place to start with for creating a new application or adding a new feature. To add a new route a new array element needs to be added to `$routes` variable.

Currently, there are two types of routing:
- Simple: routes do not have parameters, therefore it is faster to be processed.
- Regex: routes have got parameters and regular expression is used to extract the parameters, therefore it is slower to be processed.

Also each type can contain different request methods such as `POST`, `GET`, etc. For example, if you need to add a simple `GET` route for `Page` component it should be like this:
``` php
private $routes = [
    'simple' => [
        'GET' => [
            '/welcome' => [
                'component' => 'page',
                'controller' => 'Page'
            ]
        ]
    ]
]
```
In the following table all the required details for a route are explained:

<table width='100%'>
<thead>
<tr><th>Name</th><th>Type</th><th>Required</th><th>Description</th></tr>
</thead>
<tbody>
<tr>
<td>component</td>
<td>String</td>
<td>Yes</td>
<td>Name of the component e.g. page</td>
</tr>
<tr>
<td>controller</td>
<td>String</td>
<td>Yes</td>
<td>Name of the controller e.g. Page</td>
</tr>
<tr>
<td>action</td>
<td>String</td>
<td>No</td>
<td>Name of the action e.g. register. If it is not specified index is considered as the default action</td>
</tr>
<tr>
<td>accessRole</td>
<td>String</td>
<td>No</td>
<td>Specify which role can access the route e.g. guest. Default roles are defined in Config file</td>
</tr>
<tr>
<td>base</td>
<td>String</td>
<td>No</td>
<td>This needs to be specified specially if there is optional parameters, if you need to use the base section of a route. e.g. '/admin/members' is the base for '/account/members/page/{page:int}/limit/{limit:int:?}'. This is useful for example if pager is used and for some reason page and limit are not passed</td>
</tr>
</tbody>
</table>

<a name="parameters"></a>
## Passing Parameters
To pass parameters for example an `id`, route should be specified as a regex route:
``` php
private $routes = [
    'regex' => [
        'GET' => [
            '/account/member/{id:int}' => [
                'component' => 'user',
                'controller' => 'MemberUser',
                'action' => 'viewChild'
            ]
        ]
    ]
]
```

In this way `id` will be available in `viewChild()` function located in `CodeJetter/components/user/controllers/MemberUserController.php`:
``` php
$id = $this->getURLParameters()['id'];
```

Also `int` specifies that `id` must be integer otherwise it does not route to the action. To specify optional parameters you should add question mark (?) to the route:
```
private $routes = [
    'regex' => [
        'GET' => [
            '/account/members/page/{page:int}/limit/{limit:int:?}' => [
                'component' => 'user',
                'controller' => 'MemberUser',
                'action' => 'listUsers',
                'base' => '/account/members'
            ],
        ]
    ]
]
```

Please remember that optional parameters can only be applied to the last route parameter.