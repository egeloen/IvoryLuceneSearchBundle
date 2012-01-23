<?php

namespace Ivory\LuceneSearchBundle\Tests\DependencyInjection;

use Ivory\LuceneSearchBundle\Tests\Emulation\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Lucene search service test
 *
 * @author GeLo <geloen.eric@gmail.com>
 */
class LuceneSearchServiceTest extends WebTestCase
{
    /**
     * Checks the lucene search service without configuration
     */
    public function testLuceneSearchServiceWithoutConfiguration()
    {
        $luceneManager = self::createContainer()->get('ivory_lucene_search');
        
        $this->assertInstanceOf('Ivory\LuceneSearchBundle\Model\LuceneManager', $luceneManager);
    }
    
    /**
     * Checks the lucene search service with configuration
     */
    public function testLuceneSearchServiceWithConfiguration()
    {
        $pathTest = __DIR__.'/../directory_test';
        
        $filesystem = new Filesystem();
        $filesystem->remove($pathTest);
        
        $luceneManager = self::createContainer(array('environment' => 'test'))->get('ivory_lucene_search');
        
        $this->assertInstanceOf('Ivory\LuceneSearchBundle\Model\LuceneManager', $luceneManager);
        
        $luceneManager->getIndex('identifier');
        $this->assertInstanceOf('Zend\Search\Lucene\Analysis\Analyzer\Common\TextNum\CaseInsensitive', \Zend\Search\Lucene\Analysis\Analyzer\Analyzer::getDefault());
        $this->assertEquals($luceneManager->getIndex('identifier')->getMaxBufferedDocs(), 100);
        $this->assertEquals($luceneManager->getIndex('identifier')->getMaxMergeDocs(), 1000);
        $this->assertEquals($luceneManager->getIndex('identifier')->getMergeFactor(), 50);
        $this->assertEquals(\Zend\Search\Lucene\Storage\Directory\Filesystem::getDefaultFilePermissions(), 0666);
        
        $filesystem->remove($pathTest);
    }
}
