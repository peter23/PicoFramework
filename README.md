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
- `app/views` - templates
- `app/custom.php` - place for app-wide code
- `app/modules` - modules/models
- `s` - directory for static files. Can be changed in "paths" config.
- `system` - core files
- `system/core.php` - core (see below Core)
- `system/fluentpdo` - third-party library - SQL query builder
- `index.php` - entry point

### Routing

Framework takes a route from a config "paths", an element "ROUTE". Then it searches for the appropriate controller. Better to explain by example. Let's ROUTE = `/hello/world/123`. Then it will try to include files in the following order:

1) `app/controllers/hello/world/123.php`
2) `app/controllers/hello/world/123/_default.php`
3) `app/controllers/hello/world.php`
This controller will be used only if there is "/hello/world" element in "qparam_controllers" config. Also "123" will be put in `$_QPARAM` variable (in controllers scope).
If there is no "/hello/world" in "qparam_controllers" config, then processing of this route will be stopped and LoadException will be thrown.
4) `app/controllers/hello/world/_default.php`. QParam mechanism will work similarly to the point 3.
5) `app/controllers/hello.php`. Similarly to the point 3, but it will need "/hello" in "qparam_controllers" config and `$_QPARAM` will be equal to "world/123".
6) `app/controllers/hello/_default.php`. Similarly to the point 5.
7) `app/controllers/_default.php`. Similarly to the point 3, but it will need "/" in "qparam_controllers" config and `$_QPARAM` will be equal to "hello/world/123".

If the file is not exists, then it will just go to the next point (do not QParam checks). If the file is exists then it will stop anyway (after including of the file or by LoadException).


### Core
