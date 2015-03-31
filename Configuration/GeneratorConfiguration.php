<?php

namespace Pim\Bundle\DataGeneratorBundle\Configuration;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Generator configuration
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GeneratorConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data_generator');

        $rootNode
            ->children()
                ->scalarNode('output_dir')->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('entities')
                    ->isRequired()
                    ->children()
                        ->arrayNode('currencies')
                            ->children()
                                ->integerNode('count')->min(1)->isRequired()->end()
                                ->arrayNode('force_currencies')
                                    ->prototype('scalar')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('channels')
                            ->children()
                                ->integerNode('count')->min(1)->isRequired()->end()
                                ->integerNode('locales_count')->min(1)->defaultValue(1)->end()
                                ->integerNode('currencies_count')->min(1)->defaultValue(1)->end()
                                ->arrayNode('force_channels')
                                    ->prototype('scalar')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('attributes')
                            ->children()
                                ->integerNode('count')->min(1)->isRequired()->end()
                                ->scalarNode('identifier_attribute')->isRequired()->end()
                                ->arrayNode('force_attributes')
                                    ->prototype('scalar')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('families')
                            ->children()
                                ->integerNode('count')->min(1)->isRequired()->end()
                                ->integerNode('attributes_count')->min(1)->defaultValue(30)->end()
                                ->scalarNode('identifier_attribute')->isRequired()->end()
                                ->scalarNode('label_attribute')->isRequired()->end()
                                ->integerNode('requirements_count')->min(1)->defaultValue(5)->end()
                            ->end()
                        ->end()
                        ->arrayNode('products')
                            ->children()
                                ->integerNode('count')->min(1)->isRequired()->end()
                                ->integerNode('values_count')->min(1)->isRequired()->end()
                                ->integerNode('values_count_standard_deviation')->min(1)->defaultValue(10)->end()
                                ->arrayNode('mandatory_attributes')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->scalarNode('delimiter')->defaultValue(',')->end()
                                ->arrayNode('force_values')
                                    ->prototype('scalar')
                                    ->end()
                                ->end()
                                ->integerNode('start_index')->min(0)->defaultValue(0)->end()
                                ->integerNode('categories_count')->min(0)->defaultValue(0)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
