<?php

namespace Ivory\LuceneSearchBundle\Tests\Model;

use Ivory\LuceneSearchBundle\Model\Document;

/**
 * Lucene document test
 *
 * @author GeLo <geloen.eric@gmail.com>
 */
class DocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Checks the lucene document instance
     */
    public function testInstance()
    {
        $documentTest = new Document();
        $this->assertInstanceOf('Zend\Search\Lucene\Document', $documentTest);
    }
}
