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

There are several system configurations to make on a newly configured Linux system.

For arch linux, install php, php-fpm, nginx 
pacman -S php php-fpm nginx xdebug yarn
in web-site root (not public_html) 

mkdir -p private/sites/default private/sites/pcan web  

in private, create composer.json
```json
"name": "rynn/hub-site",
	"repositories" : [
		{
			"type":"vcs",
			"url" : "https://github.com/betrixed/pcan-fatfree.git"
		},
		{
			"type":"vcs",
			"url": "https://github.com/betrixed/fatfree.git"
		}
	]
	,
	"require": {
		"betrixed/pcan" : "dev-use_php5",
		"betrixed/fatfree" : "dev-use_php5",
		"matthiasmullie/minify": "^1.3"
	}
```

ln -s to vhost_nginx.conf, for a nginx server configuation.
setup an ssl certificate - 
from https://wiki.archlinux.org/index.php/Nginx
mkdir /etc/nginx/ssl
cd /etc/nginx/ssl
openssl req -new -x509 -nodes -newkey rsa:4096 -keyout server.key -out server.crt -days 1095
chmod 400 server.key
chmod 444 server.crt

If your development environment is in your home directory tree,
configure the php-fpm user and group to be yours, edit  /etc/php/php-fpm.d/www.conf

Set up internal ip like 127.0.0.5 pcan.test in /etc/hosts file.
Edit host_nginx.conf to match

In this case "pcan" URL pcan.test, web folder /web/pcan,  in private/sites/pcan/vhost_nginx.conf
The private/sites/<folder> used by PHP is called in /web/index.php , will be initially "default".

More than one nginx alias can be used, and multiple <site>_index.php in /web/
This wouldn't work on production sites, one site per client id, with seperate unix user/group ids.
But it allows for some resource sharing on a development machine.

The initial setup requires some initial resources installed in /web, otherwise
the database setup page won't be very clever.

cd ../web
yarn add bulma
yarn add jquery

Other useful yarn packages to fetch
yarn add bootstrap popper.js flatpickr jquery-form salvattore summernote

For now just the usual index.php.

```php
require_once '../private/vendor/autoload.php';
// Set up paths to webroot, parent of sites folder, and folder name of site
$app = \WC\App::run_app(__DIR__,  dirname(__DIR__) . '/private', 'default');
```