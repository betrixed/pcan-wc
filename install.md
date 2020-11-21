# Steps to setup new site
## Create Directory Structure

Typical build strategy have a local development, testing workstation
folder, which will be copied, or git cloned to the production server.

This assumes that the public accessible folder,  "web", is a subfolder of root. 
Most of the code is in a sibling folder called "private".
Make a root project folder, if there isn't one yet.

Assumptions - PHP 7.4 is installed.
With extensions -  psr, phalcon, mysql, gd, mbstring, pdo_mysql.

    mkdir proj-1
    # Inside the root folder, create "web" and "private"
    cd proj-1
    mkdir  private

Php sites typically user the "composer" utility to get 
all sorts of scripted frameworks
and amazing utilities. PHP script versions are a moving target. 
Repository details are not doable from composer command line.



For development from latest git hub, a composer.json needs to customized.  
Most of these can be individual commands, but source code repositories need
special configuration.


    {  "name": "rynn/proj-1",

		"description": "Class based examples, simple content management to build on Phacon 4.0 framework!",
		"homepage": "http://climatebet.me/",
		"license": "GPL-3.0-or-later",
		"repositories" : [
		    {
		    "type": "git",
		    "url": "https://git.climatebet.me:/git/pcan-wc.git"
		    }
		]
		,
		"require": {
		    "php": ">=7.4",
		    "betrixed/pcan" : "dev-master",
		    "matthiasmullie/minify": "^1.3",
		    "masterminds/html5": "^2.7",
		    "phalcon/devtools": "^4.0",
		    "ezyang/htmlpurifier": "^4.13",
		    "phpmailer/phpmailer": "^6.1"
		}
    }
Run this from "private".  

    cd private
    composer update

PHP utility "composer" can be installed using a linux distribution packaging tool.
Composer by default creates a "vendor" folder in the directory it is run from.
Several code repositories specified in composer.json are installed inside 
the "vendor" folder, along any direct dependencies.
 
A web server like apache or nginx, (and growing list of others), is usually setup to 
execute a file called "index.php" placed at the root of the web folder.

Populate new web folder with start up content.
	#  back to project root 
	cd ..
    cp -r private/vendor/betrixed/pcan/web web

$WEB_DIR is a variable that is assigned the value of directory path "\_\_DIR\_\_", as 
of the script, as discovered by the php interpreter.   
This is handy, as the value can be retrieved anywhere in called functions
by declaring 

     global $WEB_DIR;   // access a value declared in a top-level part of script.

Its not considered good to have "global values" accessed all over the place, 
in myriad scripts, so the plan is to declare PHP object to store
all such configuration parameters, by name, in one place.  PHP does provide 
a \$\_GLOBALS array, along with other "super globals" as access method, 
but this won't be used here either.  

The variable \$WEB_DIR isn't a strict global, but a variable created on the
stack of the first script called, as all such script declared variables are.
If such a script is called from within a function, the variable instance will disappear
when the calling function exits. So "global" means 
in the top level of the first script called.
 
It doesn't exist at the beginning of script execution. In theory, it disappears when
execution leaves "index.php", which is the end of handling the web server request.

	<?php
	/**
	* Development mode, so show all errors and warnings
	*/
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	$PHP_DIR = __DIR__;
	$TARGET = "DEV";
    $SITE_FOLDER = "proj-1";
    require_once __DIR__ . '/vendor/betrixed/pcan/site.php;

On a production site, a "site_target.php' will be called.  This is still a top level script,
\$PHP_DIR captures the path of the root directory of php scripts
 for later configuration access. \$SITE_FOLDER stores a path name 
to all configuration data of  the current site.  From this, the sites script will work
out all the other details for dyanamic paged website.

The "super global" $_GLOBALS isn't necessary to access these top-level named variables
of the called script, and only gets created on demand by PHP, that is,
if it is actually referenced by code in a script.

A web framework start up script makes all sorts of choices, and tries to make them
consistant by following "conventions". These include where particular configurations
are found and how they are declared.




