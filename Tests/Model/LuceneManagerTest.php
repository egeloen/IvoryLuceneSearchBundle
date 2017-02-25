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
use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Analysis\Analyzer\Common\TextNum\CaseInsensitive;
use ZendSearch\Lucene\Index;
use ZendSearch\Lucene\Search\QueryParser;
use ZendSearch\Lucene\Storage\Directory\Filesystem;

/**
 * @author GeLo <geloen.eric@gmail.com>
 */
class LuceneManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $paths;

    /**
     * @var LuceneManager
     */
    private $luceneManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->paths = [
            sys_get_temp_dir().'/'.uniqid().'1',
            sys_get_temp_dir().'/'.uniqid().'2',
        ];

        $this->luceneManager = new LuceneManager();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        foreach ($this->luceneManager->getIndexes() as $index) {
            $this->luceneManager->eraseIndex($index);
        }
    }

    public function testHasIndexWithIndex()
    {
        $this->luceneManager->setIndex('identifier', $this->paths[0]);
        $this->assertTrue($this->luceneManager->hasIndex('identifier'));
    }

    public function testHasIndexWithoutIndex()
    {
        $this->assertFalse($this->luceneManager->hasIndex('foo'));
    }

    public function testGetIndexWithIndex()
    {
        $this->luceneManager->setIndex('identifier', $this->paths[0]);

        $this->assertInstanceOf(Index::class, $this->luceneManager->getIndex('identifier'));
        $this->assertTrue(file_exists($this->paths[0]));
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
            $this->paths[0],
            CaseInsensitive::class,
            100,
            1000000,
            5,
            0666,
            false,
            'UTF-8'
        );

        $index = $this->luceneManager->getIndex('identifier');

        $this->assertInstanceOf(CaseInsensitive::class, Analyzer::getDefault());
        $this->assertSame(100, $index->getMaxBufferedDocs());
        $this->assertSame(1000000, $index->getMaxMergeDocs());
        $this->assertSame(5, $index->getMergeFactor());
        $this->assertSame(0666, Filesystem::getDefaultFilePermissions());
        $this->assertSame('UTF-8', QueryParser::getDefaultEncoding());
    }

    public function testRemoveIndex()
    {
        $this->luceneManager->setIndex('identifier', $this->paths[0]);
        $this->luceneManager->getIndex('identifier');
        $this->luceneManager->removeIndex('identifier');
        $this->assertTrue(file_exists($this->paths[0]));

        $this->luceneManager->setIndex('identifier', $this->paths[0]);
        $this->luceneManager->getIndex('identifier');
        $this->luceneManager->removeIndex('identifier', true);
        $this->assertFalse(file_exists($this->paths[0]));
    }

    public function testEraseIndex()
    {
        $this->luceneManager->setIndex('identifier', $this->paths[0]);
        $this->luceneManager->getIndex('identifier');
        $this->assertTrue(file_exists($this->paths[0]));

        $this->luceneManager->eraseIndex('identifier');
        $this->assertFalse(file_exists($this->paths[0]));
    }

    public function testSetIndexesWithValidValues()
    {
        $this->luceneManager->setIndexes($indexes = [
            'identifier1' => [
                'path'                  => $this->paths[0],
                'analyzer'              => CaseInsensitive::class,
                'max_buffered_docs'     => 100,
                'max_merge_docs'        => 1000000,
                'merge_factor'          => 5,
                'permissions'           => 0666,
                'auto_optimized'        => true,
                'query_parser_encoding' => 'UTF-8',
            ],
            'identifier2' => [
                'path' => $this->paths[1],
            ],
        ]);

        $this->assertSame(array_keys($indexes), $this->luceneManager->getIndexes());

        $index1 = $this->luceneManager->getIndex('identifier1');

        $this->assertInstanceOf(CaseInsensitive::class, Analyzer::getDefault());
        $this->assertSame(100, $index1->getMaxBufferedDocs());
        $this->assertSame(1000000, $index1->getMaxMergeDocs());
        $this->assertSame(5, $index1->getMergeFactor());
        $this->assertSame(0666, Filesystem::getDefaultFilePermissions());
        $this->assertSame('UTF-8', QueryParser::getDefaultEncoding());

        $index2 = $this->luceneManager->getIndex('identifier2');

        $this->assertInstanceOf(LuceneManager::DEFAULT_ANALYZER, Analyzer::getDefault());
        $this->assertSame(LuceneManager::DEFAULT_MAX_BUFFERED_DOCS, $index2->getMaxBufferedDocs());
        $this->assertSame(LuceneManager::DEFAULT_MAX_MERGE_DOCS, $index2->getMaxMergeDocs());
        $this->assertSame(LuceneManager::DEFAULT_MERGE_FACTOR, $index2->getMergeFactor());
        $this->assertSame(LuceneManager::DEFAULT_PERMISSIONS, Filesystem::getDefaultFilePermissions());
        $this->assertSame(LuceneManager::DEFAULT_QUERY_PARSER_ENCODING, QueryParser::getDefaultEncoding());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Each lucene index must have a path value.
     */
    public function testSetIndexesWithInvalidValues()
    {
        $this->luceneManager->setIndexes(['identifier' => []]);
    }
}
