# Controllers
- [Overview](#overview)
- [Action](#action)

<a name="overview"></a>
## Overview
Controllers keep your application logic, get data using mappers and pass it to the view. A controller is named with `Controller` suffix e.g. `UserController` and is located in `controllers` folder of its component. Also every controller class must extend `BaseController` abstract class.

<a name="action"></a>
## Action
Controllers include functions that are specified in the routes as actions. For example, this route is used to display the register form:
```
private $routes = [
    'simple' => [
        'GET' => [
            '/register' => [
                'component' => 'user',
                'controller' => 'MemberUser',
                'action' => 'registerForm',
                'accessRole' => 'guest'
            ],
        ]
    ]
```

For this route in `MemberUserController` there is a function called `registerForm`:
```
/**
 * Generate register form
 *
 * @throws \Exception
 */
public function registerForm()
{
    $page = new Page($this->getRouteInfo()->getAccessRole());
    $page->setTitle('Register');

    /**
     * hi to language
     */
    $language = Registry::getLanguageClass();
    $requiredFields = $language->get('requiredFields');
    $passwordRequirements = $language->get('passwordRequirements');
    $usernameRequirements = $language->get('usernameRequirements');
    /**
     * bye to language
     */

    $componentTemplate = new ComponentTemplate();
    $componentTemplate->setTemplatePath('components/user/templates/memberRegister.php');
    $componentTemplate->setData([
        'requiredFields' => $requiredFields,
        'passwordRequirements' => $passwordRequirements,
        'usernameRequirements' => $usernameRequirements
    ]);

    (new View())->make(
        $page,
        [
            'register' => $componentTemplate
        ],
        null,
        new FormHandler('register')
    );
}
```

Let's break the above function down to the following segments:

- Page: Each view must have a page object that for example contains title, meta tags, etc. By passing access role to page constructor, meta tag is automatically set based on `accessRolesRobot` in the config.
- Language: Using a language object you can get the translations for different keys. Default language is set in the config and the language `JSON` files are located in `core/language`.
- Component template: Each view can have one to many component templates. A component template must have a template path and can contain data. This is used to pass data to the view.
- View: Finally, View puts the mentioned segments all together and generates the html content.