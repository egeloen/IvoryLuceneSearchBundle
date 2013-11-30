<?php

/*
 * This file is part of the Ivory Lucene Search package.
 *
 * (c) Eric GELOEN <geloen.eric@gmail.com>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Ivory\LuceneSearchBundle\Tests;

use Ivory\LuceneSearchBundle\IvoryLuceneSearchBundle;

/**
 * Ivory Lucene search bundle test.
 *
 * @author GeLo <geloen.eric@gmail.com>
 */
class IvoryLuceneSearchBundleTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Ivory\LuceneSearchBundle\IvoryLuceneSearchBundle*/
    protected $bundle;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->bundle = new IvoryLuceneSearchBundle();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->bundle);
    }

    public function testBundle()
    {
        $this->assertInstanceOf('Symfony\Component\HttpKernel\Bundle\Bundle', $this->bundle);
    }
}
