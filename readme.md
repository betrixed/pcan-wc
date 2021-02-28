# Pcan-wc

Php Content Management System website, which has been updated a times, somewhat aimlessly.

It needs better setup instructions with a more automated setup.

Currently requires Phalcon, fledgeling version 5, PHP-only code, without the C-extension requirements.

There are a just a few minor class path incompatibilities between Phalcon v5.0.x and the Zephir compiled C-extension for Phalcon 4.

Phalcon v5.0.x does not have Volt as a View engine, nor does it have its own ORM, or ActiveRecord classes and functions, since these classes depended on parsers embedded in the Phalcon 4 C-extension.

As stop-gap, I modified the venerable php-activerecord to work here, to substitute as Database and ActiveRecord support, a layer over the PDO extensions.
The php-activerecord lacks some sophistication and rigor found in the likes of Propel, but it was easy to update and plug in.

Uses my forked version Phalcon 5 (branch v5.0.x) found here at [https://github/betrixed/phalcon.git](https://github/betrixed/phalcon.git)

My forked version of php-activerecord , modified for PHP 8.0 is here at [https://github/betrixed/php-activerecord.git](https://github/betrixed/php-activerecord.git)




