# PicoFramework

Just a try to make really fast and light (and stupid) framework.

- MVC- or MVMC-architecture (first M - Model or Module, second M - Middleware)
- Modular structure with very lightweight core
- Filesystem-based routing
- Auto HTML escaping of the data for views by default
- Minimum of OOP
- Lazy-load of everything

### Files structure

- `app` - an application directory
- `app/config` - configuration files
- `app/config/db.php` - a database config
- `app/config/paths.php` - a paths config
- `app/config/qparam_controllers.php` - list of controllers that can accept a part of the route as a parameter (see below Routing)
- `app/controllers` - controllers. Controllers paths are defining routes (see below Routing).
- `app/controllers/_404.php` - 404 page
- `app/controllers/_default.php` - a controller for "/". Basically it is optional file.
- `app/middlewares` - middlewares (see below Middlewares).
- `app/views` - templates
- `app/custom.php` - a place for the app-wide code
- `app/modules` - modules/models
- `s` - a directory for static files. Can be changed in the "paths" config.
- `system` - core files
- `system/core.php` - the core (see below Core)
- `system/PicoDatabase` - SQL query builder and MySQLi wrapper
- `index.php` - entry point

All files from the list above are required. There are also other files (modules, middlewares, etc.), which just contain some sample code.

### Routing

Framework takes a route from a config "paths", an element "ROUTE". Then it searches for the appropriate controller. Better to explain by an example. Let's ROUTE = `/hello/world/123`. Then it will try to include files in the following order:

1) `app/controllers/hello/world/123.php`

2) `app/controllers/hello/world/123/_default.php`

3) `app/controllers/hello/world.php`
This controller will be used only if there is an "/hello/world" element in the "qparam_controllers" config. Also "123" will be put in `$_QPARAM` variable (in controller's scope).
If there is no "/hello/world" in the "qparam_controllers" config, then processing of this route will be stopped and LoadException will be thrown.

4) `app/controllers/hello/world/_default.php`. QParam mechanism will work similarly to the point 3.

5) `app/controllers/hello.php`. Similarly to the point 3, but it will need "/hello" in the "qparam_controllers" config and `$_QPARAM` will be equal to "world/123".

6) `app/controllers/hello/_default.php`. Similarly to the point 5.

7) `app/controllers/_default.php`. Similarly to the point 3, but it will need "/" in the "qparam_controllers" config and `$_QPARAM` will be equal to "hello/world/123".

If the file is not exists, then it will just go to the next point (do not QParam checks). If the file is exists then it will stop anyway (after including the file or by LoadException).

### Middlewares

A middleware can know which controller is processed from the variable `$_QNAME`.

`_init` is processed at first, `_default` at last.


### Core

- `processRequest ($query)`. Takes the query and runs an appropriate controller or /_404. It is automatically called from index.php.

- `allowIncludeFile ($file)`. Returns true if it is safe to `include()` `$file`. Checks for "../", "/.."" and existing of the `$file`.

- `getConfig ($name[, $param])`. Loads (just `include()`s) config from `app/config` or takes it from the memory and returns it. The config file should look like this: `<?php
	return <something>;
`, where `<something>` is an array or just some value.

- `runController ($route[, $data])`. Runs (just `include()`s) an appropriate controller by the given route. Processes routing. `extract()`s `$data` to controller's scope.

- `runView ($name[, $data])`. Runs (just `include()`s) specified view. `extract()`s html-escaped `$data` to view's scope.

- `initDatabase ()`. Creates and returns new instance (does not use existing) of PicoDatabase.

- `getModule ($name[, $data])`. Modules are php files, which should contain class Module\_$name. All special characters in `$name` will be replaced with "\_". That class can extend BaseModule, which just provides $this->DB variable with PicoDatabase instance. Any module object will be created only once, further calls will receive existing instance (like singleton).

- `dataRepo ($key[, $val])`. Simple function to store data in the memory. Basically it is used just instead of a global variable. If `$val` is specified, then it will store the value. Otherwise it will return stored value (or null).

- `_U ($q[, $params])`. Returns URL of controller `$q` with GET parameters `$params`. `$params` can be a string or an array. It considers "BASE\_URL" from the "paths" config.

- `_US ($q)`. Returns URL of the static file `$q`. It considers "STATIC\_BASE\_URL" from the "paths" config.

- `htmlEscape ($s)`. Returns `$s` with applied htmlspecialchars function. Also works with arrays. This function is automatically applied to `$data` in `runView`.

- `dontHtmlEscape ($s)`. Returns `$s`, which is marked as a value which should not be escaped.

- `formatException ($e)`. Returns the exception `$e` in human-readable view. Does not use HTML or HTML escaping.
