# Installation

## Symfony >= 2.1

Before starting, I inform you the installation with Composer is a little tricky due to `zendframework/zendsearch` not 
stable yet. The last tag is an RC one and since composer uses the stable flag as minimum stability, you need to 
explicitly configure the Zend search library. The following instruction will allow you to install the latest RC version:

``` json
{
    "require": {
        "egeloen/lucene-search-bundle": "^2.0",
        "zendframework/zendsearch": "^2.0@rc"
    }
}
```

If you prefer install the latest development version, you need to use this rewrite rule in order to be compliant 
with the bundle requirement:

``` json
{
    "require": {
        "egeloen/lucene-search-bundle": "^2.0",
        "zendframework/zendsearch": "dev-master as 2.0.0-RC5"
    }
}
```

Then, just need to install it:

``` bash
$ composer update
```

## Symfony 2.0.*

For simplicity, we will install the main Zend framework which wraps the Zend Lucene Search component. If you prefer,
you can only install this component & this dependencies.

### Add respectively Zend & IvoryLuceneSearchBundle to your vendor/ & vendor/bundles/ directories

#### Using the vendors script

Add the following lines in your ``deps`` file

```
[zend]
    git=http://git.zendframework.com/zf.git

[IvoryLuceneSearchBundle]
    git=http://github.com/egeloen/IvoryLuceneSearchBundle.git
    target=/bundles/Ivory/LuceneSearchBundle
```

Run the vendors script

``` bash
$ php bin/vendors update
```

#### Using submodules

``` bash
$ git submodule add http://git.zendframework.com/zf.git vendor
$ git submodule add http://github.com/egeloen/IvoryLuceneSearchBundle.git vendor/bundles/Ivory/LuceneSearchBundle
```

### Add Zend & Ivory namespaces to your autoloader

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    'Zend'  => __DIR__.'/../vendor/zend/library',
    'Ivory' => __DIR__.'/../vendor/bundles',
    // ...
);
```

### Add the IvoryLuceneSearchBundle to your application kernel

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    return array(
        new Ivory\LuceneSearchBundle\IvoryLuceneSearchBundle(),
        // ...
    );
}
```
