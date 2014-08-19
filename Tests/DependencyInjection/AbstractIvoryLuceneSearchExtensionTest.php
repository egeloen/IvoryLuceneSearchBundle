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
use Ivory\LuceneSearchBundle\Model\LuceneManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem as SfFilesystem;
use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Storage\Directory\Filesystem as ZfFilesystem;
use ZendSearch\Lucene\Search\QueryParser;

/**
 * Ivory Lucene search extension test.
 *
 * @author GeLo <geloen.eric@gmail.com>
 */
abstract class AbstractIvoryLuceneSearchExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Symfony\Component\DependencyInjection\ContainerBuilder */
    protected $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->tearDown();

        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.root_dir', __DIR__.'/../Fixtures');
        $this->container->registerExtension($lucene = new IvoryLuceneSearchExtension());
        $this->container->loadFromExtension($lucene->getAlias());
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $path = __DIR__.'/../Fixtures/directory_test';

        if (file_exists($path)) {
            $filesystem = new SfFilesystem();
            $filesystem->remove($path);
        }

        unset($this->container);
    }

    /**
     * Gets the config provider.
     *
     * @return array The config provider.
     */
    public function configProvider()
    {
        return array(
            array(
                'default',
                array(
                    'analyzer'              => LuceneManager::DEFAULT_ANALYZER,
                    'max_buffered_docs'     => LuceneManager::DEFAULT_MAX_BUFFERED_DOCS,
                    'max_merge_docs'        => LuceneManager::DEFAULT_MAX_MERGE_DOCS,
                    'merge_factor'          => LuceneManager::DEFAULT_MERGE_FACTOR,
                    'permissions'           => LuceneManager::DEFAULT_PERMISSIONS,
                    'query_parser_encoding' => LuceneManager::DEFAULT_QUERY_PARSER_ENCODING,
                ),
            ),
            array(
                'custom',
                array(
                    'analyzer'              => 'ZendSearch\Lucene\Analysis\Analyzer\Common\TextNum\CaseInsensitive',
                    'max_buffered_docs'     => 100,
                    'max_merge_docs'        => 1000,
                    'merge_factor'          => 50,
                    'permissions'           => 0666,
                    'query_parser_encoding' => 'UTF-8',
                ),
            )
        );
    }

    /**
     * Loads a configuration.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container     The container.
     * @param string                                                  $configuration The configuration.
     */
    abstract protected function loadConfiguration(ContainerBuilder $container, $configuration);

    public function testManagerWithoutConfiguration()
    {
        $this->container->compile();

        $luceneManager = $this->container->get('ivory_lucene_search');

        $this->assertInstanceOf('Ivory\LuceneSearchBundle\Model\LuceneManager', $luceneManager);
        $this->assertFalse($luceneManager->hasIndexes());
    }

    /**
     * @dataProvider configProvider
     */
    public function testManagerWithConfiguration($name, array $config)
    {
        $this->loadConfiguration($this->container, $name);
        $this->container->compile();

        $luceneManager = $this->container->get('ivory_lucene_search');

        $this->assertInstanceOf('Ivory\LuceneSearchBundle\Model\LuceneManager', $luceneManager);
        $this->assertTrue($luceneManager->hasIndexes());

        $index = $luceneManager->getIndex('identifier');

        $this->assertInstanceOf($config['analyzer'], Analyzer::getDefault());
        $this->assertSame($config['max_buffered_docs'], $index->getMaxBufferedDocs());
        $this->assertSame($config['max_merge_docs'], $index->getMaxMergeDocs());
        $this->assertSame($config['merge_factor'], $index->getMergeFactor());
        $this->assertSame($config['permissions'], ZfFilesystem::getDefaultFilePermissions());
        $this->assertSame($config['query_parser_encoding'], QueryParser::getDefaultEncoding());
    }
}
