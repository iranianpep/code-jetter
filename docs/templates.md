# Templates
- [Overview](#overview)
- [Accessing Data](#data)
- [HTML Content](#html)

<a name="overview"></a>
## Overview
Templates are used by the view for the presentation and are located in `templates` folder of its component.

<a name="data"></a>
## Accessing Data
In a template `$this` refers to `View` class and all the public functions for `View` class can be called:
```
/** @var CodeJetter\core\View $this */
$this->getFooter()->addScriptFile($this->getConfig()->get('URL') . '/scripts/chosen.jquery.min.js');
```

Also to access data passed in the controller:
```
$data = $this->getCurrentComponentTemplate()->getData();
```

<a name="html"></a>
## HTML Content
Each template must `return` HTML content, that is how `View` can generate the HTML page.