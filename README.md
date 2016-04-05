# PicoFramework

Just a try to make really fast and light (and stupid) framework.

- MVC-architecture (M - Model or Module)
- Modular structure with very lightweight core
- Filesystem-based routing
- Auto HTML escaping of the data for views by default
- Minimum of OOP
- Lazy-load of everything

### Files structure

- `app` - application directory
- `app/config` - configuration files
- `app/config/db.php` - database config
- `app/config/paths.php` - paths config
- `app/config/qparam_controllers.php` - list of controllers that can accept part of route as parameter (see below Routing)
- `app/controllers` - controllers. Paths to controllers define routes (see below Routing).
- `app/controllers/_404.php` - 404 page
- `app/controllers/_default.php` - controller for "/"
- `middlewares`
- `app/views` - templates
- `app/custom.php` - place for app-wide code
- `app/modules` - modules/models
- `s` - directory for static files. Can be changed in "paths" config.
- `system` - core files
- `system/core.php` - core (see below Core)
- `system/PicoDatabase` - SQL query builder and MySQLi wrapper
- `index.php` - entry point

### Routing

Framework takes a route from a config "paths", an element "ROUTE". Then it searches for the appropriate controller. Better to explain by example. Let's ROUTE = `/hello/world/123`. Then it will try to include files in the following order:

1) `app/controllers/hello/world/123.php`

2) `app/controllers/hello/world/123/_default.php`

3) `app/controllers/hello/world.php`
This controller will be used only if there is "/hello/world" element in "qparam_controllers" config. Also "123" will be put in `$_QPARAM` variable (in controller's scope).
If there is no "/hello/world" in "qparam_controllers" config, then processing of this route will be stopped and LoadException will be thrown.

4) `app/controllers/hello/world/_default.php`. QParam mechanism will work similarly to the point 3.

5) `app/controllers/hello.php`. Similarly to the point 3, but it will need "/hello" in "qparam_controllers" config and `$_QPARAM` will be equal to "world/123".

6) `app/controllers/hello/_default.php`. Similarly to the point 5.

7) `app/controllers/_default.php`. Similarly to the point 3, but it will need "/" in "qparam_controllers" config and `$_QPARAM` will be equal to "hello/world/123".

If the file is not exists, then it will just go to the next point (do not QParam checks). If the file is exists then it will stop anyway (after including of the file or by LoadException).


### Core

- `processRequest ($query)`. Takes the query and runs appropriate controller or /_404. It is automatically called from index.php.

- `getConfig ($name[, $param])`. Loads (just `include()`s) config from `app/config` or takes it from the cache and returns it. Config file should look like this: `<?php
	return <something>;
`, where `<something>` is array or just some value.

- `runController ($route[, $data])`. Runs (just `include()`s) appropriate controller by the given route. Processes routing. `extract()`s `$data` to controller's scope.

- `getRunController ($route[, $data])`. Calls `runController` and returns its output as a variable.

- `runView ($name[, $data])`. Runs (just `include()`s) specified view. `extract()`s html escaped `$data` to view's scope.

- `getRunView ($name[, $data])`. Calls `runView` and returns its output as a variable.

- `getModule ($name)`. Modules are php files, which should contain class Module\_$name. All special characters in $name will be replaced with "\_". That class can extends BaseModule, which just provides $class->DB variable with PicoDatabase instance. Any module will be created only once, further calls will receive existing instance (singleton).

- `_U ($q[, $params])`. Returns URL of controller $q with GET parameters $params. $params can be a string or an array. It considers BASE\_URL from "paths" config.

- `_US($q)`. Returns URL of static file. It considers STATIC\_BASE\_URL from "paths" config.

- `htmlEscape($s)`. Returns $s with applied htmlspecialchars function. Also works with arrays. This function is automatically applied to $data in runView.

- `dontHtmlEscape($s)`. Returns $s, which is marked as value which should not be escaped.
