<?php

namespace Ivory\LuceneSearchBundle\Tests\Model;

use Ivory\LuceneSearchBundle\Model\LuceneManager;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Lucene manager test
 *
 * @author GeLo <geloen.eric@gmail.com>
 */
class LuceneManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Ivory\LuceneSearchBundle\Model\LuceneManager
     */
    protected static $luceneManager = null;
    
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        self::$luceneManager = new LuceneManager();
    }
    
    /**
     * Checks the check index method
     */
    public function testHasIndex()
    {
        $this->assertFalse(self::$luceneManager->hasIndex('foo'));
        
        self::$luceneManager->addIndex('identifier', '/path/to/lucene/index');
        $this->assertTrue(self::$luceneManager->hasIndex('identifier'));
        
        $this->setExpectedException('InvalidArgumentException');
        self::$luceneManager->hasIndex(0);
    }
    
    /**
     * Checks the index getter method
     */
    public function testGetIndex()
    {
        $pathTest = __DIR__.'/../directory_test';
        
        $filesystem = new Filesystem();
        $filesystem->remove($pathTest);
        
        self::$luceneManager->addIndex('identifier', $pathTest);
        $this->assertInstanceOf('Zend\Search\Lucene\Index', self::$luceneManager->getIndex('identifier'));
        $this->assertTrue(file_exists($pathTest));
        
        $filesystem->remove($pathTest);
        
        $this->setExpectedException('InvalidArgumentException');
        self::$luceneManager->getIndex('foo');
    }
    
    /**
     * Checks the indexes setter method
     */
    public function testSetIndexes()
    {
        $pathTests = array(__DIR__.'/../directory_test1', __DIR__.'/../directory_test2');
        
        $filesytem = new Filesystem();
        foreach($pathTests as $pathTest)
            $filesytem->remove($pathTest);
        
        self::$luceneManager->setIndexes(array(
            'identifier1' => array(
                'path' => $pathTests[0],
                'analyzer' => 'Zend\Search\Lucene\Analysis\Analyzer\Common\TextNum\CaseInsensitive',
                'max_buffered_docs' => 100,
                'max_merge_docs' => 1000000,
                'merge_factor' => 5,
                'permissions' => 0666,
                'auto_optimized' => true
            ),
            'identifier2' => array(
                'path' => $pathTests[1]
            )
        ));

        self::$luceneManager->getIndex('identifier1');
        $this->assertInstanceOf('Zend\Search\Lucene\Analysis\Analyzer\Common\TextNum\CaseInsensitive', \Zend\Search\Lucene\Analysis\Analyzer\Analyzer::getDefault());
        $this->assertEquals(self::$luceneManager->getIndex('identifier1')->getMaxBufferedDocs(), 100);
        $this->assertEquals(self::$luceneManager->getIndex('identifier1')->getMaxMergeDocs(), 1000000);
        $this->assertEquals(self::$luceneManager->getIndex('identifier1')->getMergeFactor(), 5);
        $this->assertEquals(\Zend\Search\Lucene\Storage\Directory\Filesystem::getDefaultFilePermissions(), 0666);
        
        self::$luceneManager->getIndex('identifier2');
        $this->assertInstanceOf('Zend\Search\Lucene\Analysis\Analyzer\Common\Text\CaseInsensitive', \Zend\Search\Lucene\Analysis\Analyzer\Analyzer::getDefault());
        $this->assertEquals(self::$luceneManager->getIndex('identifier2')->getMaxBufferedDocs(), 10);
        $this->assertEquals(self::$luceneManager->getIndex('identifier2')->getMaxMergeDocs(), PHP_INT_MAX);
        $this->assertEquals(self::$luceneManager->getIndex('identifier2')->getMergeFactor(), 10);
        $this->assertEquals(\Zend\Search\Lucene\Storage\Directory\Filesystem::getDefaultFilePermissions(), 0777);
        
        foreach($pathTests as $pathTest)
            $filesytem->remove($pathTest);
        
        $this->setExpectedException('InvalidArgumentException');
        self::$luceneManager->setIndexes(array(
            'identifier' => array()
        ));
    }
    
    /**
     * Checks the index add method
     */
    public function testAddIndex()
    {
        $pathTest = __DIR__.'/../directory_test';
        
        $filesystem = new Filesystem();
        $filesystem->remove($pathTest);
        
        self::$luceneManager->addIndex('identifier', $pathTest, 'Zend\Search\Lucene\Analysis\Analyzer\Common\TextNum\CaseInsensitive', 100, 1000000, 5, 0666);
        self::$luceneManager->getIndex('identifier');
        $this->assertInstanceOf('Zend\Search\Lucene\Analysis\Analyzer\Common\TextNum\CaseInsensitive', \Zend\Search\Lucene\Analysis\Analyzer\Analyzer::getDefault());
        $this->assertEquals(self::$luceneManager->getIndex('identifier')->getMaxBufferedDocs(), 100);
        $this->assertEquals(self::$luceneManager->getIndex('identifier')->getMaxMergeDocs(), 1000000);
        $this->assertEquals(self::$luceneManager->getIndex('identifier')->getMergeFactor(), 5);
        $this->assertEquals(\Zend\Search\Lucene\Storage\Directory\Filesystem::getDefaultFilePermissions(), 0666);
        
        $filesystem->remove($pathTest);
    }
    
    /**
     * Checks the index remove method
     */
    public function testRemoveIndex()
    {   
        $pathTest = __DIR__.'/../directory_test';
        
        $filesystem = new Filesystem();
        $filesystem->remove($pathTest);
        
        self::$luceneManager->addIndex('identifier', $pathTest);
        self::$luceneManager->getIndex('identifier');
        self::$luceneManager->removeIndex('identifier');
        $this->assertTrue(file_exists($pathTest));
        
        self::$luceneManager->addIndex('identifier', $pathTest);
        self::$luceneManager->getIndex('identifier');
        self::$luceneManager->removeIndex('identifier', true);
        $this->assertFalse(file_exists($pathTest));
    }
    
    /**
     * Checks the index erase method
     */
    public function testEraseIndex()
    {
        $pathTest = __DIR__.'/../directory_test';
        
        $filesystem = new Filesystem();
        $filesystem->remove($pathTest);
        
        self::$luceneManager->addIndex('identifier', $pathTest);
        self::$luceneManager->getIndex('identifier');
        $this->assertTrue(file_exists($pathTest));
        
        self::$luceneManager->eraseIndex('identifier');
        $this->assertFalse(file_exists($pathTest));
        
        self::$luceneManager->getIndex('identifier');
        
        $filesystem->remove($pathTest);
    }
}
