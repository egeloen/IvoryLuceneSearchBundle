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
 * @author GeLo <geloen.eric@gmail.com>
 */
class IvoryLuceneSearchBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IvoryLuceneSearchBundle
     */
    private $bundle;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->bundle = new IvoryLuceneSearchBundle();
    }

    public function testBundle()
    {
        $this->assertInstanceOf('Symfony\Component\HttpKernel\Bundle\Bundle', $this->bundle);
    }
}
