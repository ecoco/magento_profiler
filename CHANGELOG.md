CHANGELOG
=========

1.3.0
-----
* introduced a settings section which will allow adjust some settings to your needs
* improved ssl handling
* improved handling of "xdebug.file_link_format"
* improved context display to make use of "file_link_format"
* added timings to event collector
* made tables sortable
* added profile size in MB to search results
* added purge profile button to search result page
* introduced a replacement for the Varien_Profiler
* introduced new "Performance" Tab

1.2.1
-----
* fix for #1 Not working in secure environment with magento 1.9.2 (https://)
* fix for #6 Undefined index error in layout data collector 
* added magento 1.9.3 to test stack

1.2.0
-----
* fixed an exception in debug mode when block output is suppressed with "disable module output"
* fixed an exception when logging and monolog is not installed
* unified call stack rendering
* improved request collector to capture also redirects, session data and flash messages
* improved support for "xdebug.file_link_format" to also work with urls like "http://localhost:63342/api/file/%f:%l"

1.1.0
-----
* improved logger, will now also display deprecations
* added unit tests
* small toolbar adjustments
* general clean up
* cleaned up overwrite logic

1.0.1
-----

* added the new "model" collector
* minor improvements into the documentation
* small stability fixes
* improved event data collector + display, now also showing the observers 
* added clear/enable/disable caches actions to the toolbar
