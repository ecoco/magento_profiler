# Ecocode Profiler - Magento 1.x Web Profiler

The ecocode profiler provides a development toolbar for Magento which displays a wide range of metrics and page load data for all the pages of the shop. It gives you direct access to the page's database queries, memory usage, events, requests, layout rendering, translation resolution and many other useful statistics. It is also easily extendable if you need to track additional metrics.

This profiler is based on the awesome [Symfony WebProfiler][4].
The concept and code of the WebProfiler have been ported to assist with Magento as much as possible.


## Requirements
* php >= 5.4
* magento < 2

Tested with magento 
1.7, 1.8. 1.9

---
![Toolbar](/docs/image/toolbar.jpg "Toolbar")


![Profiler](/docs/image/profiler.jpg "Profiler")


## Installation

If you have not already configured [magento-composer-installer][1] add
```
"extra": {
   "magento-root-dir": "httpdocs/"
 }
```
to your **composer.json**. If your magento root dir is the same directory as the one containing your **composer.json** use `"."` as the `magento-root-dir`

`composer require-dev ecocode/magento-profiler`

### Webserver Config
It might be necessary to extend your webserver config to handle "dev.php" correctly

### Nginx:
If you are using nginx and get a `404` for the profiler, try adding the following to your nginx config before the php location definition:
```
location /dev.php/ {
    rewrite / /dev.php;
}
```
---
#### Apache:
nothing to do here, should run out of the box

## Usage
The profiler is only enabled if you open your shop via `http://myshop.local/dev.php/`.
The idea is to develop always in dev mode alias "dev.php" and only switch back to "production" from
time to time to verify the result.

## Features
* Improved exception handling in dev mode with the [symfony/debug][2]. No more checking the log files!
* Easily extendable, just add a new **collector** via your configuration

## Collectors
* Request/Response
  * Display of request/response server parameters
* Memory
  * Display of memory usage 
* Mysql
  * Display of all queries with syntax highlighting and stack traces to locate the origin
  * Queries by context so you can easily determine the origin block
  * Detection of identical queries that can be avoided
  * Metrics for "mysql crud" operations
  * Support for multiple database connections
* Events
  * Display of all events that have been fired during page load
  * List of all called observers
* Ajax
  * Recording of ajax calls
* Customer
  * Display of customer group and tax class 
* Layout
  * Metrics including created and rendered blocks and total rendering time
  * List of layout handlers used
  * List of blocks created but not rendered
  * Call graph including rendering times by block, including and excluding children
* Translations
  * Display of translations that are defined, missing, invalid or are using a fallback
* Rewrites
  * Detection of rewrites and rewrite conflicts (credits to [magen98-magerun][3] for the detection)
* Logs
  * Display of all `Mage::log` calls
* Models
  * Display of all model load, delete and save calls
  * Detection of "load" calls within loops!
* Cache
  * Display of current cache configuration including the option to enable/disable and flush from the profiler
  * Display of all cache calls including not-for-cache **hits** and **misses**
* Configuration
  * Base PHP configuration
  * Option to view `phpinfo()`
  * Basic magento configuration
  * Display of enabled and disabled modules

## Security
It should be safe to add this module to your own vcs by default. The profiler
is only active when you are visiting your page via "dev.php", which is restricted to 
localhost by default.

If you are using a vm you can edit the `dev.php` or set `$_SERVER['ALLOW_PROFILER'] = 1` 
via your nginx or htaccess

## TODO
* Session Data
* Rewrites
* Improve docs
* "how to extend"



## Mixed
If you get a `gateway timeout 503` instead of an error message please try to adjust your
nginx config
```
http {
    ...
    fastcgi_buffers 8 16k;
    fastcgi_buffer_size 32k;
    ...
}
```

## Thanks to
* [symfony/debug][2] for the awesome debug component
* [magen98-magerun][3] for the rewrite conflict detection
* [Symfony WebProfiler][4]


[1]: https://github.com/Cotya/magento-composer-installer
[2]: https://github.com/symfony/debug
[3]: https://github.com/netz98/n98-magerun
[4]: https://github.com/symfony/web-profiler-bundle