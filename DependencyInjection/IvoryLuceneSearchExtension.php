<?php

namespace Ivory\LuceneSearchBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * Ivory lucene search extension
 *
 * @author GeLo <geloen.eric@gmail.com>
 */
class IvoryLuceneSearchExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();

        $config = $processor->processConfiguration($configuration, $configs);
        
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        foreach(array('services.xml') as $file)
            $loader->load($file);

        $this->loadIndexes($config, $container);
    }
    
    /**
     * Loads indexees configuration
     *
     * @param array $config
     * @param ContainerBuilder $container 
     */
    protected function loadIndexes(array $config, ContainerBuilder $container)
    {
        $container->setParameter('ivory_lucene_search.indexes', $config);
    }
}
