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

### Core
