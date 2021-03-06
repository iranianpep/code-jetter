# Getting Started
- [Check The Requirements](#requirements)
- [Apply Database Changes](#database)
- [Create The Config File](#config)
- [Create The Route File](#route)
- [Move Code Jetter Files To Your Host](#copy-files)
- [Include Autoloader In The Index](#autoloader)
- [Run Composer](#composer)
- [Troubleshooting](#toubleshooting)
- [Where To Go From Here](#where)

<a name="requirements"></a>
## Check The Requirements
Currently the main requirement for Code Jetter to run is PHP 5.6+. If your PHP version is not 5.6 or above that, an error will be thrown.

<a name="database"></a>
## Apply Database Changes
If you need to use user component you must create all the required tables and also add an admin user to start with. This can be done by running `CodeJetter/sql/initial.sql` in your MySQL database. Once it is finished you will see the following tables:
- `cj_admin_users`
- `cj_group_member_user_xref`
- `cj_member_groups`
- `cj_member_users`

Also an admin user has been added to `cj_admin_users`
Username: `admin`
Password: `Admin1`

As you see the default tables prefix is `cj` which is specified in your config file (explained in the next step) with the key: `tablePrefix`. This can be changed if you need to.

<a name="config"></a>
## Create The Config File
At this stage you need to change `CodeJetter/Config.Template.php` as you need and then rename the file AND class names to `Config.php` and `Config` respectively. For the time being, you only need to worry about these config parameters:

<table width='100%'>
<thead>
<tr><th>Name</th><th>Type</th><th>Required</th><th>Description</th></tr>
</thead>
<tbody>
<tr>
<td>URL</td>
<td>String</td>
<td>Yes</td>
<td>The domain url</td>
</tr>
<tr>
<td>URI</td>
<td>String</td>
<td>Yes</td>
<td>Path to `CodeJetter` folder</td>
</tr>
<tr>
<td>defaultDB</td>
<td>String</td>
<td>Yes</td>
<td>The domain url</td>
</tr>
<tr>
<td>databases</td>
<td>Array</td>
<td>Yes</td>
<td>This includes the default database details</td>
</tr>
</tbody>
</table>

<a name="route"></a>
## Create The Route File
The same as the config file change `CodeJetter/Routes.Template.php` file and `RoutesTemplate` class names to `CodeJetter/Routes.php` and `Routes` respectively.

<a name="copy-files"></a>
## Move Code Jetter Files To Your Host
Finally copy the `CodeJetter/public` folder **content** to your host public directory (`WWW` or `public_html`), and `CodeJetter` folder (excluding `public` folder) to a non-public directory which should match the `URI` value in the config file. Please note that the downloaded folder from GitHub is named `code-jetter`, so you need to rename it to `CodeJetter`.

<a name="autoloader"></a>
## Include Autoloader In The Index
Open `public/index.php` and change the path to `autoloader.php` based on the place that `CodeJetter/autoloader.php` is located. Now if you check `URL` in the browser you will see the welcome page.

<a name="composer"></a>
## Run Composer
If you do not have `Composer` first you need to install it. Then run it the in the project root to download all the required libraries.

<a name="toubleshooting"></a>
## Troubleshooting
- If there is any server error (error numbers starting with 5) first check `public/.htaccess` file. Then check to see if your host public path is set correctly. Finally you might need to check your server logs.
- If the loaded page in the browser is blank you should enable PHP error reporting by changing `debug` to `true` for `prod` environment in the config file. Once the issue is resolved make sure to change `debug` back to `false` for security reasons.
- You can also check `CodeJetter/temp/custom_error_log.log` to see if any error has been recorded

<a name="where"></a>
## Where To Go From Here
If you are ready to create your application using Code Jetter these are more detailed documentations that can help you:

- <a href='https://github.com/iranianpep/code-jetter/blob/master/docs/components.md'>Components</a>
- <a href='https://github.com/iranianpep/code-jetter/blob/master/docs/routing.md'>Routing</a>
- <a href='https://github.com/iranianpep/code-jetter/blob/master/docs/controllers.md'>Controllers</a>
- <a href='https://github.com/iranianpep/code-jetter/blob/master/docs/mappers.md'>Mappers</a>
- <a href='https://github.com/iranianpep/code-jetter/blob/master/docs/models.md'>Models</a>
- <a href='https://github.com/iranianpep/code-jetter/blob/master/docs/templates.md'>Templates</a>
