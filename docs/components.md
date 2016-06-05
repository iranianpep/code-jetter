# Components
- [Overview](#overview)
- [Controller](#controller)
- [Mapper](#mapper)
- [Template](#template)

<a name="overview"></a>
## Overview
Components are mainly used to extend the framework functionality e.g. User management. A component is located in `components` folder and may have `controllers`, `mappers`, `models` and `templates` folders.

<a name="controller"></a>
## Controller
Controllers keep your application logic, get data using mappers and pass it to the view. A controller is named with `Controller` suffix e.g. `UserController` and is located in `controllers` folder.

<a name="mapper"></a>
## Mapper
Mappers are responsible to fetch data from the database, map it to the relevant model and return objects. A mapper is named with `Mapper` suffix e.g. `UserMapper` and is located in `mappers` folder.

<a name="model"></a>
## Model
Models represent a data entity and also contain the business logic. A model is named the same as its controller or mapper without `Controller` or `Mapper` at the end and is located in `models` folder.

<a name="template"></a>
## Template
Templates are used by the view for the presentation and are located in `templates` folder.


