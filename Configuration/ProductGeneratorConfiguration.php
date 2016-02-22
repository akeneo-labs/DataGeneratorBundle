<?php

namespace Pim\Bundle\DataGeneratorBundle\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Product Generator configuration
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductGeneratorConfiguration implements ConfigurationInterface
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
                ->integerNode('seed')->defaultValue(null)->end()
                ->arrayNode('entities')
                    ->isRequired()
                    ->children()
                        ->arrayNode('products')
                            ->children()
                                ->scalarNode('filename')->end()
                                ->integerNode('count')->min(1)->isRequired()->end()
                                ->integerNode('filled_attributes_count')->min(1)->isRequired()->end()
                                ->integerNode('filled_attributes_standard_deviation')->min(1)->defaultValue(10)->end()
                                ->arrayNode('mandatory_attributes')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->scalarNode('delimiter')->defaultValue(';')->end()
                                ->arrayNode('force_values')
                                    ->prototype('scalar')
                                    ->end()
                                ->end()
                                ->integerNode('start_index')->min(0)->defaultValue(0)->end()
                                ->integerNode('categories_count')->min(0)->defaultValue(0)->end()
                                ->integerNode('products_per_variant_group')->min(0)->defaultValue(0)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
