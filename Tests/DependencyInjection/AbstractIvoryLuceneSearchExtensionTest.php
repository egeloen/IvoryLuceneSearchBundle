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
use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Analysis\Analyzer\Common\TextNum\CaseInsensitive;
use ZendSearch\Lucene\Search\QueryParser;
use ZendSearch\Lucene\Storage\Directory\Filesystem;

/**
 * @author GeLo <geloen.eric@gmail.com>
 */
abstract class AbstractIvoryLuceneSearchExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
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
        $luceneManager = $this->container->get('ivory_lucene_search');

        foreach ($luceneManager->getIndexes() as $index) {
            $luceneManager->eraseIndex($index);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $configuration
     */
    abstract protected function loadConfiguration(ContainerBuilder $container, $configuration);

    public function testManagerWithoutConfiguration()
    {
        $this->container->compile();

        $luceneManager = $this->container->get('ivory_lucene_search');

        $this->assertInstanceOf(LuceneManager::class, $luceneManager);
        $this->assertFalse($luceneManager->hasIndexes());
    }

    /**
     * @param string $name
     * @param array  $config
     *
     * @dataProvider configProvider
     */
    public function testManagerWithConfiguration($name, array $config)
    {
        $this->loadConfiguration($this->container, $name);
        $this->container->compile();

        $luceneManager = $this->container->get('ivory_lucene_search');

        $this->assertInstanceOf(LuceneManager::class, $luceneManager);
        $this->assertTrue($luceneManager->hasIndexes());

        $index = $luceneManager->getIndex('identifier');

        $this->assertInstanceOf($config['analyzer'], Analyzer::getDefault());
        $this->assertSame($config['max_buffered_docs'], $index->getMaxBufferedDocs());
        $this->assertSame($config['max_merge_docs'], $index->getMaxMergeDocs());
        $this->assertSame($config['merge_factor'], $index->getMergeFactor());
        $this->assertSame($config['permissions'], Filesystem::getDefaultFilePermissions());
        $this->assertSame($config['query_parser_encoding'], QueryParser::getDefaultEncoding());
    }

    /**
     * @return array
     */
    public function configProvider()
    {
        return [
            [
                'default',
                [
                    'analyzer'              => LuceneManager::DEFAULT_ANALYZER,
                    'max_buffered_docs'     => LuceneManager::DEFAULT_MAX_BUFFERED_DOCS,
                    'max_merge_docs'        => LuceneManager::DEFAULT_MAX_MERGE_DOCS,
                    'merge_factor'          => LuceneManager::DEFAULT_MERGE_FACTOR,
                    'permissions'           => LuceneManager::DEFAULT_PERMISSIONS,
                    'query_parser_encoding' => LuceneManager::DEFAULT_QUERY_PARSER_ENCODING,
                ],
            ],
            [
                'custom',
                [
                    'analyzer'              => CaseInsensitive::class,
                    'max_buffered_docs'     => 100,
                    'max_merge_docs'        => 1000,
                    'merge_factor'          => 50,
                    'permissions'           => 0666,
                    'query_parser_encoding' => 'UTF-8',
                ],
            ],
        ];
    }
}
