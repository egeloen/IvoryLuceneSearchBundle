<?php

/*
 * This file is part of the Ivory Lucene Search package.
 *
 * (c) Eric GELOEN <geloen.eric@gmail.com>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Ivory\LuceneSearchBundle\Tests\DependencyInjection;

use Ivory\LuceneSearchBundle\DependencyInjection\IvoryLuceneSearchExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem as SfFilesystem;
use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Storage\Directory\Filesystem as ZfFilesystem;

/**
 * Ivory Lucene search extension test.
 *
 * @author GeLo <geloen.eric@gmail.com>
 */
abstract class AbstractIvoryLuceneSearchExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Symfony\Component\DependencyInjection\ContainerBuilder */
    private $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.root_dir', __DIR__.'/Fixtures');
        $this->container->registerExtension(new IvoryLuceneSearchExtension());
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->container);
    }

    /**
     * Loads a configuration.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container     The container.
     * @param string                                                  $configuration The configuration.
     */
    abstract protected function loadConfiguration(ContainerBuilder $container, $configuration);

    public function testLuceneSearchServiceWithoutConfiguration()
    {
        $this->loadConfiguration($this->container, 'empty');
        $this->container->compile();

        $luceneManager = $this->container->get('ivory_lucene_search');

        $this->assertInstanceOf('Ivory\LuceneSearchBundle\Model\LuceneManager', $luceneManager);
    }

    public function testSingleIndex()
    {
        $this->loadConfiguration($this->container, 'single');
        $this->container->compile();

        $pathTest = __DIR__.'/Fixtures/directory_test';

        $filesystem = new SfFilesystem();
        $filesystem->remove($pathTest);

        $luceneManager = $this->container->get('ivory_lucene_search');

        $this->assertInstanceOf('Ivory\LuceneSearchBundle\Model\LuceneManager', $luceneManager);

        $index = $luceneManager->getIndex('identifier');

        $this->assertInstanceOf(
            'ZendSearch\Lucene\Analysis\Analyzer\Common\TextNum\CaseInsensitive',
            Analyzer::getDefault()
        );

        $this->assertSame(100, $index->getMaxBufferedDocs());
        $this->assertSame(1000, $index->getMaxMergeDocs());
        $this->assertSame(50, $index->getMergeFactor());
        $this->assertEquals(0666, ZfFilesystem::getDefaultFilePermissions());

        $filesystem->remove($pathTest);
    }
}
