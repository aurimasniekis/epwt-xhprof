EPWTXhprofBundle
================

[![Latest Stable Version](https://poser.pugx.org/epwt/xhprof/version.svg)](https://packagist.org/packages/epwt/xhprof) [![Latest Unstable Version](https://poser.pugx.org/epwt/xhprof/v/unstable.svg)](//packagist.org/packages/epwt/xhprof) [![Total Downloads](https://poser.pugx.org/epwt/xhprof/downloads.svg)](https://packagist.org/packages/epwt/xhprof)

EPWTXhprofBundle provides XHProf integration to Symfony profiler and wrapper for XHProf simple usage anywhere in project.

[![Toolbar](Resources/meta/toolbar.small.png?raw=true "Toolbar")](Resources/meta/toolbar.png)

[![Samples List](Resources/meta/samples_list.small.png?raw=true "Samples List")](Resources/meta/samples_list.png) [![Sample Runs](Resources/meta/sample_runs.small.png?raw=true "Sample Runs")](Resources/meta/sample_runs.png) [![Sample Run](Resources/meta/sample_run.small.png?raw=true "Sample Run")](Resources/meta/sample_run.png)

## Requirements

 * Symfony >= 2.3
 * PHP >= 5.4
 * Facebook XHProf Extension

## Install via Composer

```
composer require --dev epwt/xhprof "~1.0"
```

## Setting up

Register EPWTXhprofBundle in AppKernel.php file. I suggest to use it only in development or testing environment

### AppKernel.php

```php
public function registerBundles()
{
	if (in_array($this->getEnvironment(), array('dev', 'test'))) {
		$bundles[] = new EPWT\XhprofBundle\EPWTXhprofBundle();
	}
}
```


## Usage

To use XHProf anywhere in project just initiate profiling by using global function:

```php
xhprofStart('Name of sample');
```

To end profiling use:

```php
xhprofEnd();
```

## Sample Usage

```php
for($a = 0; $a < 20; $a++) {
    xhprofStart('Hello world');
    for ($i = 0; $i < 20; $i ++) {
        sleep(0.1);
    }
    xhprofEnd();
}
```

License
-------

This bundle is under the MIT license. See the complete license in the bundle:

    Resources/meta/LICENSE

About
-----

EPWTXhprofBundle is brought to you by [Aurimas Niekis](https://github.com/gcds).

Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/gcds/epwt-xhprof/issues).

When reporting a bug, it may be a good idea to reproduce it in a basic project
built using the [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
to allow developers of the bundle to reproduce the issue by simply cloning it
and following some steps.