# Installation

Before starting, I inform you the installation with Composer is a little tricky due to `zendframework/zendsearch` not 
stable yet. The last tag is an RC one and since composer uses the stable flag as minimum stability, you need to 
explicitly configure the Zend search library. The following instruction will allow you to install the latest RC version:

``` json
{
    "require": {
        "egeloen/lucene-search-bundle": "^3.0",
        "zendframework/zendsearch": "^2.0@rc"
    }
}
```

If you prefer install the latest development version, you need to use this rewrite rule in order to be compliant 
with the bundle requirement:

``` json
{
    "require": {
        "egeloen/lucene-search-bundle": "^3.0",
        "zendframework/zendsearch": "dev-master as 2.0.0-RC5"
    }
}
```

Then, just need to install it:

``` bash
$ composer update
```
