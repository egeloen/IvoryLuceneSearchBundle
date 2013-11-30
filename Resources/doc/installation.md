# Installation

## Symfony >= 2.1

Before starting, I inform you the installation with Composer is a little tricky due to missing packages/tags on the
Zend Lucene Search official repository. Basically, the `zendframework/zendsearch` is not registered on packagist &
additionally, one of its dependencies (`zendframework/zend-stdlib`) is not registered too.

So, before requiring something, you need to register this two repositories in your `composer.json`:

``` json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/zendframework/ZendSearch"
        },
        {
            "type": "vcs",
            "url": "https://github.com/zendframework/Component_ZendStdlib"
        }
    ]
}
```

Now, Composer is aware of these two packages. The second issue is about tag as the last one of the
`zendframework/zendsearch` is `2.0.0-RC5` which requires itself `zendframework/zend-stdlib` as `self.version`
(same as `2.0.0-RC5`). The following instruction will allow you to install the latest stable releases of each packages:

``` json
{
    "require": {
        "egeloen/lucene-search-bundle": "dev-master",
        "zendframework/zendsearch": "2.0.0-rc5",
        "zendframework/zend-stdlib": "2.2.5 as 2.0.0-rc5"
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

### Using submodules

``` bash
$ git submodule add http://git.zendframework.com/zf.git vendor
$ git submodule add http://github.com/egeloen/IvoryLuceneSearchBundle.git vendor/bundles/Ivory/LuceneSearchBundle
```

## Add Zend & Ivory namespaces to your autoloader

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    'Zend'  => __DIR__.'/../vendor/zend/library',
    'Ivory' => __DIR__.'/../vendor/bundles',
    // ...
);
```

## Add the IvoryLuceneSearchBundle to your application kernel

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

Next : [Usage](http://github.com/egeloen/IvoryLuceneSearchBundle/blob/master/Resources/doc/usage.md)
