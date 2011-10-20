# Installation

## Add respectively Zend & IvoryLuceneSearchBundle to your vendor/ & vendor/bundles/ directories

### Using the vendors script

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
