<?php

namespace Ivory\LuceneSearchBundle\Tests\Model;

use Ivory\LuceneSearchBundle\Model\Field;

/**
 * Lucene field test
 *
 * @author GeLo <geloen.eric@gmail.com>
 */
class FieldTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Checks the lucene field instance
     */
    public function testInstance()
    {
        $fieldTest = new Field('name', 'value', 'utf-8', true, true, true);
        $this->assertInstanceOf('Zend\Search\Lucene\Document\Field', $fieldTest);
    }
}
