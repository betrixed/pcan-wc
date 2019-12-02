# Pcan-fatfree

Php Content Management System, with Fat-Free Framework used as a black box (Fat free is installed using Composer).

DB Schema setup can be applied to Mysql or Postgresql backends.

# Why Fat-Free?

Fat-Free, current version 3.6, is a stable framework that efficiently provides all the basic PHP facilities for several patterns of web site construction.
Its design features include:-
* Global registry management for core data and routing.
* Code makes efficient use of regular expressions, in handling routes, something that PHP is really good at.
* Support for common database providers and basic table record objects.
* Source code and API stability.
* Respectable benchmark performance for a PHP framework.
* Flexible Extensibility

# The Setup for Pcan
    * Presumes a web root directory for index.php, and folders for static Assets of .css, .js and image files. 
    * A protected site source code and configuration folder, for privileged access by the web site programs.

The private folder is the root of the .git repository. The web folder inside the repository contains what should be copied to the public web root.

The location of the private folder is indicated by the relative path in first line of index.php.

```php
    require '../private/bootstrap.php';
    $f3 = init_app(__DIR__, 'default');
    try {
        $f3->run();
    } catch (Exception $x) {
        echo $x->getMessage();
    }
```

The bootstrap.php declares the function init_app, which is called to configure fat-free variables and routes and returns its Base::instance() object. 
The run() function is called, and simple exceptions handled.

The file path to the root of the private directory is in the $f3 variable 'php'.
Most directory paths are stored with a directory separator termination.

```php
$php = $f3->get('php'); // root of private
$mysite = $php + 'sites/mysite/'

```

The folder 'sites' inside 'private' contains examples of sites. Initially the value of 'mysite' is 'default', which allows access to the setup of a mysqsl or pgsql database and
and default site folder and admin login.
There is only one active site folder configuration at any time, indicated by the name passed to function init_app(). 
Different folders in sites are to allow for setup and or maintainance, and different sites or versions maintained in the one .git repository.

The name of the site is also used as a subfolder of the web root, to hold asset and theme customisations for that site.



