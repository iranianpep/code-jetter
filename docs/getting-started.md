# Getting Started
- [Check The Requirements](#requirements)
- [Apply Database Changes](#database)
- [Create The Config](#config)
- [Add Your First Route](#router)
- [Move Code Jetter Folder To Your Hosting Space](#copy-folder)

<a name="requirements"></a>
## Check The Requirements
Currently the main requirement for Code Jetter to run is PHP 5.6+. If your PHP version is not above 5.6, an error will be thrown.

<a name="database"></a>
## Apply Database Changes
If you need to use user component you must create all the required tables and also add an admin user to start with.
This can be done by running initial.sql (located in sql folder) in your MySQL database. If it is finished successfully 4 tables:
- cj_admin_users
- cj_group_member_user_xref
- cj_member_groups
- cj_member_users

And an admin user with the following details will be added to the database"
Username: admin
Password: Admin1

As you see the default tables prefix is cj which is specified in your config file with the key: tablePrefix. This can be changed if you need to.

<a name="config"></a>
## Create The Config
At this stage you need to change core/Config.Template.php as you need and then rename the file AND class names to Config.php and Config respectively.

For the time being, you only need to worry about these parameters in the config file:
- `URL`
- `URI`
- `defaultDB`
- `databases` which includes the default database details

<a name="router"></a>
## Add Your First Route
Add your first route in core/Router.php

<a name="copy-folder"></a>
## Move Code Jetter Folder To Your Hosting Space
Finally copy the `public` folder content to your hosting public directory (WWW or public_html), and CodeJetter folder to a non-public directory which should match the `URI` value in the config file.

http://stackoverflow.com/questions/26146152/spl-autoload-fault-on-remote-server
http://stackoverflow.com/questions/15027486/php-spl-autoload-and-namespaces-doesnt-work-with-capital-letters