# Usage

Before starting, I recommend you to read the lucene's documentation available [here](http://framework.zend.com/manual/en/zend.search.lucene.html).

The bundle allows you to configure & request multiple indexes which are identified by a unique identifier.
Each index can be configured & manipulated like an original zend lucene index.

## Configure an index

### By configuration file

By configuration file is for me the easy way to use this bundle. 
It allows you to describe the index configuration one time & you will be able to request the configured index easily.

This configuration requires only one value which is the index storage path.

```
# app/config/config.yml

ivory_lucene_search:
    # Index identifier
    indentifier1:
        # Path to store the index (Required)
        path: "/path/to/store/lucene/index1"

    # Index identifier
    identifier2:
        # Path to store the index (Required)
        path: "/path/to/store/lucene/index2"

        # Index analyser (Optional)
        # See http://framework.zend.com/manual/en/zend.search.lucene.charset.html
        analyzer: Zend\Search\Lucene\Analysis\Analyzer\Common\Text\CaseInsensitive

        # Max Buffered documents (Optional)
        # See http://framework.zend.com/manual/en/zend.search.lucene.index-creation.html#zend.search.lucene.index-creation.optimization.maxbuffereddocs
        max_buffered_docs: 10

        # Max merged documents (Optional)
        # See http://framework.zend.com/manual/en/zend.search.lucene.index-creation.html#zend.search.lucene.index-creation.optimization.maxmergedocs
        max_merged_docs: 10000 # (default: PHP_INT_MAX)

        # Merge factor (Optional)
        # See http://framework.zend.com/manual/en/zend.search.lucene.index-creation.html#zend.search.lucene.index-creation.optimization.mergefactor
        merge_factor: 10

        # Index directory permission (Optional)
        # See http://framework.zend.com/manual/en/zend.search.lucene.index-creation.html#zend.search.lucene.index-creation.permissions
        permissions: 0777

        # Auto optmized flag (Optional)
        # If this flag is true, each time you request an index, it will be optmized
        # See http://framework.zend.com/manual/en/zend.search.lucene.index-creation.html#zend.search.lucene.index-creation.optimization
        auto_optimized: false
```

### By coding

``` php
<?php

/**
 * Request the ivory lucene search service
 * 
 * @var Ivory\LuceneSearchBundle\Model\LuceneManager $luceneSearch
 */
$luceneSearch = $this->get('ivory_lucene_search');

// Set all indexes to the lucene search
$indexes = array(
    'identifier1' => array(
        'path' => '/path/to/store/lucene/index1'
    ),
    'identifier2' => array(
        'path' => '/path/to/store/lucene/index2',
        'analyzer' => 'Zend\Search\Lucene\Analysis\Analyzer\Common\Text\CaseInsensitive',
        'max_buffered_docs' => 10,
        'max_merged_docs' => PHP_INT_MAX,
        'merge_factor' => 10,
        'permissions' => 0777,
        'auto_optimized' => true
    )
);

$luceneSearch->setIndexes($indexes);

// Or add each index one by one to the lucene search
// $luceneSearch->addIndex($identifier, $path, $analyzer = 'Zend\Search\Lucene\Analysis\Analyzer\Common\Text\CaseInsensitive', $maxBufferedDocs = 10, $maxMergeDocs = PHP_INT_MAX, $mergeFactor = 10, $permissions = 0777, $autoOptimized = false)
$luceneSearch->addIndex('identifier1', '/path/to/store/lucene/index1');
$luceneSearch->addIndex('identifier2', '/path/to/store/lucene/index2', 'Zend\Search\Lucene\Analysis\Analyzer\Common\Text\CaseInsensitive', 10, PHP_INT_MAX, 10, 0777, false);
```

## Request an index

``` php
<?php

/**
 * Request a configured index
 * 
 * @var Zend\Search\Lucene\Index $index Lucene index
 */
$index = $this->get('ivory_lucene_search')->getIndex('identifier1');
    
```

It is important to know that each time you request an index, it is loaded. 
So if you request an index, add your datas, update the configuration & request again the same index, the lucene index will use your new configuration.

## Unregister an index

``` php
<?php

$luceneSearch->removeIndex('identifier1');
```

## Erase an index

``` php
<?php

$luceneSearch->eraseIndex('identifier1');
```

## Unregister & erase an index

``` php
<?php

$luceneSearch->removeIndex('identifier1', true);
```

## Simple usage of lucene

### Create a document

``` php
<?php

use Ivory\LuceneSearchBundle\Model\Document;
use Ivory\LuceneSearchBundle\Model\Field;

// Request an index
$index = $this->get('ivory_lucene_search')->getIndex('identifier');

// Create a new document
$document = new Document();
$document->addField(Field::keyword('field1', 'Keyword'));
$document->addField(Field::text('field2', 'Some text'));

// Add your document to the index
$index->addDocument($document);

// Commit your change
$index->commit();

// If you want you can optimize your index
$index->optimize();
```

You can add many other sources to your index like describes in the zend lucene documentation.

### Find documents

``` php
<?php

/**
 * Find all documents
 * 
 * @var array $documents
 */
$documents = $this->get('ivory_lucene_search')->getIndex('identifier')->find('Keywork some text');

// Access finded datas
foreach($documents as $document)
{
    // Acces field value
    $field1 = $document->field1;
    $field2 = $document->field2;
}
```

You can request much more precisly your index like describes in the zend documentation.

Previous : [Installation](http://github.com/egeloen/IvoryLuceneSearchBundle/blob/master/Resources/doc/installation.md)
Next : [Test](http://github.com/egeloen/IvoryLuceneSearchBundle/blob/master/Resources/doc/test.md)
