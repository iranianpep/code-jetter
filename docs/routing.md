In Code Jetter all the routes are located in `core/Router.php` and that is probably the first place to start with for creating a new application or adding a new feature. To add a new route a new array element needs to be added to `$routes` variable.

Currently, there are two types of routing:
- Simple: routes do not have parameters, therefore it is faster to be processed.
- Regex: routes have got parameters and regular expression is used to extract the parameters, therefore it is slower to be processed.

Also each type can contain different request methods such as `POST`, `GET`, etc. For example, if you need to add a simple `GET` route it should be like this:
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