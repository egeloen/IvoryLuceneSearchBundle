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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Ivory lucene search bundle configuration.
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
        $rootNode = $treeBuilder->root('ivory_lucene_search');

        $this->addIndexesSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Add indexes section.
     *
     * @param Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node The root node.
     */
    protected function addIndexesSection(ArrayNodeDefinition $node)
    {
        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('path')
                        ->isRequired()
                    ->end()
                    ->scalarNode('analyzer')
                        ->defaultValue('ZendSearch\Lucene\Analysis\Analyzer\Common\Text\CaseInsensitive')
                    ->end()
                    ->scalarNode('max_buffered_docs')
                        ->defaultValue(10)
                    ->end()
                    ->scalarNode('max_merge_docs')
                        ->defaultValue(PHP_INT_MAX)
                    ->end()
                    ->scalarNode('merge_factor')
                        ->defaultValue(10)
                    ->end()
                    ->scalarNode('permissions')
                        ->defaultValue(0777)
                    ->end()
                    ->scalarNode('auto_optimized')
                        ->defaultFalse()
                    ->end()
                ->end()
            ->end();
    }
}
