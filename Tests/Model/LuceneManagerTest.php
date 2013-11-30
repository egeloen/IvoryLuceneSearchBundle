<?php

/*
 * This file is part of the Ivory Lucene Search package.
 *
 * (c) Eric GELOEN <geloen.eric@gmail.com>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Ivory\LuceneSearchBundle\Tests\Model;

use Ivory\LuceneSearchBundle\Model\LuceneManager;
use Symfony\Component\Filesystem\Filesystem as SfFilesystem;
use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Storage\Directory\Filesystem as ZfFilesystem;

/**
 * Lucene manager test.
 *
 * @author GeLo <geloen.eric@gmail.com>
 */
class LuceneManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    private $path;

    /** @var \Ivory\LuceneSearchBundle\Model\LuceneManager */
    private $luceneManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->path = sys_get_temp_dir().'/'.uniqid();
        $this->tearDown();

        $this->luceneManager = new LuceneManager();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        if (file_exists($this->path)) {
            $filesystem = new SfFilesystem();
            $filesystem->remove($this->path);
        }

        unset($this->luceneManager);
    }

    public function testHasIndexWithIndex()
    {
        $this->luceneManager->setIndex('identifier', '/path/to/lucene/index');
        $this->assertTrue($this->luceneManager->hasIndex('identifier'));
    }

    public function testHasIndexWithoutIndex()
    {
        $this->assertFalse($this->luceneManager->hasIndex('foo'));
    }

    public function testGetIndexWithIndex()
    {
        $this->luceneManager->setIndex('identifier', $this->path);

        $this->assertInstanceOf('ZendSearch\Lucene\Index', $this->luceneManager->getIndex('identifier'));
        $this->assertTrue(file_exists($this->path));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The lucene index "foo" does not exist.
     */
    public function testGetIndexWithoutIndex()
    {
        $this->luceneManager->getIndex('foo');
    }

    public function testSetIndex()
    {
        $this->luceneManager->setIndex(
            'identifier',
            $this->path,
            'ZendSearch\Lucene\Analysis\Analyzer\Common\TextNum\CaseInsensitive',
            100,
            1000000,
            5,
            0666
        );

        $this->luceneManager->getIndex('identifier');
        $this->assertInstanceOf('ZendSearch\Lucene\Analysis\Analyzer\Common\TextNum\CaseInsensitive', Analyzer::getDefault());
        $this->assertSame(100, $this->luceneManager->getIndex('identifier')->getMaxBufferedDocs());
        $this->assertSame(1000000, $this->luceneManager->getIndex('identifier')->getMaxMergeDocs());
        $this->assertSame(5, $this->luceneManager->getIndex('identifier')->getMergeFactor());
        $this->assertSame(0666, ZfFilesystem::getDefaultFilePermissions());
    }

    public function testRemoveIndex()
    {
        $this->luceneManager->setIndex('identifier', $this->path);
        $this->luceneManager->getIndex('identifier');
        $this->luceneManager->removeIndex('identifier');
        $this->assertTrue(file_exists($this->path));

        $this->luceneManager->setIndex('identifier', $this->path);
        $this->luceneManager->getIndex('identifier');
        $this->luceneManager->removeIndex('identifier', true);
        $this->assertFalse(file_exists($this->path));
    }

    public function testEraseIndex()
    {
        $this->luceneManager->setIndex('identifier', $this->path);
        $this->luceneManager->getIndex('identifier');
        $this->assertTrue(file_exists($this->path));

        $this->luceneManager->eraseIndex('identifier');
        $this->assertFalse(file_exists($this->path));
    }

    public function testSetIndexesWithValidValues()
    {
        $paths = array(
            sys_get_temp_dir().'/'.uniqid().'1',
            sys_get_temp_dir().'/'.uniqid().'2',
        );

        $filesytem = new SfFilesystem();

        foreach($paths as $pathTest) {
            $filesytem->remove($pathTest);
        }

        $this->luceneManager->setIndexes(array(
            'identifier1' => array(
                'path'              => $paths[0],
                'analyzer'          => 'ZendSearch\Lucene\Analysis\Analyzer\Common\TextNum\CaseInsensitive',
                'max_buffered_docs' => 100,
                'max_merge_docs'    => 1000000,
                'merge_factor'      => 5,
                'permissions'       => 0666,
                'auto_optimized'    => true,
            ),
            'identifier2' => array(
                'path' => $paths[1],
            )
        ));

        $this->luceneManager->getIndex('identifier1');
        $this->assertInstanceOf('ZendSearch\Lucene\Analysis\Analyzer\Common\TextNum\CaseInsensitive', Analyzer::getDefault());
        $this->assertSame(100, $this->luceneManager->getIndex('identifier1')->getMaxBufferedDocs());
        $this->assertSame(1000000, $this->luceneManager->getIndex('identifier1')->getMaxMergeDocs());
        $this->assertSame(5, $this->luceneManager->getIndex('identifier1')->getMergeFactor());
        $this->assertSame(0666, ZfFilesystem::getDefaultFilePermissions());

        $this->luceneManager->getIndex('identifier2');
        $this->assertInstanceOf('ZendSearch\Lucene\Analysis\Analyzer\Common\Text\CaseInsensitive', Analyzer::getDefault());
        $this->assertSame(10, $this->luceneManager->getIndex('identifier2')->getMaxBufferedDocs());
        $this->assertSame(PHP_INT_MAX, $this->luceneManager->getIndex('identifier2')->getMaxMergeDocs());
        $this->assertSame(10, $this->luceneManager->getIndex('identifier2')->getMergeFactor());
        $this->assertSame(0777, ZfFilesystem::getDefaultFilePermissions());

        foreach($paths as $pathTest) {
            $filesytem->remove($pathTest);
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Each lucene index must have a path value.
     */
    public function testSetIndexesWithInvalidValues()
    {
        $this->luceneManager->setIndexes(array(
            'identifier' => array(),
        ));
    }
}
