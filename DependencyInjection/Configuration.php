<?php

/*
 * This file is part of the Ivory Lucene Search package.
 *
 * (c) Eric GELOEN <geloen.eric@gmail.com>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Ivory\LuceneSearchBundle\DependencyInjection;

use Ivory\LuceneSearchBundle\Model\LuceneManager;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Ivory lucene search configuration.
 *
 * @author GeLo <geloen.eric@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder
            ->root('ivory_lucene_search')
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('path')
                        ->isRequired()
                    ->end()
                    ->scalarNode('analyzer')
                        ->defaultValue(LuceneManager::DEFAULT_ANALYZER)
                    ->end()
                    ->scalarNode('max_buffered_docs')
                        ->defaultValue(LuceneManager::DEFAULT_MAX_BUFFERED_DOCS)
                    ->end()
                    ->scalarNode('max_merge_docs')
                        ->defaultValue(LuceneManager::DEFAULT_MAX_MERGE_DOCS)
                    ->end()
                    ->scalarNode('merge_factor')
                        ->defaultValue(LuceneManager::DEFAULT_MERGE_FACTOR)
                    ->end()
                    ->scalarNode('permissions')
                        ->defaultValue(LuceneManager::DEFAULT_PERMISSIONS)
                    ->end()
                    ->scalarNode('auto_optimized')
                        ->defaultValue(LuceneManager::DEFAULT_AUTO_OPTIMIZED)
                    ->end()
                    ->scalarNode('query_parser_encoding')
                        ->defaultValue(LuceneManager::DEFAULT_QUERY_PARSER_ENCODING)
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
