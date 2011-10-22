<?php

namespace Ivory\LuceneSearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Ivory lucene search bundle configuration
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
     * Add indexes section
     *
     * @param Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     */
    protected function addIndexesSection(ArrayNodeDefinition $node)
    {
        $node
            ->useAttributeAsKey('ivory_lucene_search')->prototype('array')
                ->children()
                    ->scalarNode('path')->isRequired()->end()
                    ->scalarNode('analyzer')->defaultValue('Zend\Search\Lucene\Analysis\Analyzer\Common\Text\CaseInsensitive')->end()
                    ->scalarNode('max_buffered_docs')->defaultValue(10)->end()
                    ->scalarNode('max_merge_docs')->defaultValue(PHP_INT_MAX)->end()
                    ->scalarNode('merge_factor')->defaultValue(10)->end()
                    ->scalarNode('permissions')->defaultValue(0777)->end()
                    ->scalarNode('auto_optimized')->defaultFalse()->end()
                ->end()
            ->end();
    }
}
