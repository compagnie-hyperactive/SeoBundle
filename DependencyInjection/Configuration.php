<?php

namespace Lch\SeoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    const ROOT_NAMESPACE = "lch_seo";
    const ROOT_PARAMETERS_NAMESPACE = "lch.seo";

    const SITEMAP = 'sitemap';
    const STEP = 'step';
    const STEP_DEFAULT_VALUE = 0.1;

    const SPECIFIC = 'specific';
    const LOC = 'loc';
    const PRIORITY = 'priority';

    const ENTITIES = 'entities';
    const ENTITIES_EXCLUDE = 'exclude';

    const TAGS = 'tags';
    const TITLE = 'title';
    const DESCRIPTION = 'description';
    
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(static::ROOT_NAMESPACE);


        $rootNode
            ->children()
                ->arrayNode(static::SPECIFIC)
                    ->prototype('array')
                        ->children()
                            ->arrayNode(static::TAGS)
                                ->children()
                                    ->scalarNode(static::TITLE)
                                    ->end()
                                    ->scalarNode(static::DESCRIPTION)
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode(static::SITEMAP)
                                ->children()
                                    ->scalarNode(static::LOC)
                                    ->end()
                                    ->scalarNode(static::PRIORITY)
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode(static::SITEMAP)
                    ->isRequired()
                    ->children()
                        ->scalarNode(static::STEP)
                            ->defaultValue(static::STEP_DEFAULT_VALUE)
                        ->end()
                        ->arrayNode(static::ENTITIES)
                            ->prototype('array')
                                ->children()
                                    ->arrayNode(static::ENTITIES_EXCLUDE)
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
