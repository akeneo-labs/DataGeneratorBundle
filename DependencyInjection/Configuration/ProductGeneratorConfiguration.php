<?php

namespace Pim\Bundle\DataGeneratorBundle\DependencyInjection\Configuration;

use Pim\Bundle\DataGeneratorBundle\Generator\ProductGenerator;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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

        $productsNode = $this->getDraftAndProductBaseNode('products');
        $productsNode
            ->isRequired()
            ->children()
                ->integerNode('categories_count')->min(0)->defaultValue(0)->info('Number of categories per product')->end()
                ->integerNode('products_per_variant_group')->min(0)->defaultValue(0)->info('Number of products in each variant group')->end()
                ->integerNode('percentage_complete')->min(0)->max(100)->defaultValue(20)->info('Percentage of complete products')->end()
                ->booleanNode('all_attribute_keys')->defaultFalse()->info('If true exports the products with all attribute keys possible')->end()
            ->end();

        $draftsNode = $this->getDraftAndProductBaseNode('product_drafts');

        $rootNode
            ->children()
                ->scalarNode('output_dir')->isRequired()->cannotBeEmpty()->info('Directory where files will be generated')->end()
                ->integerNode('seed')->defaultValue(null)->info('Seed used to generate random values')->end()
                ->arrayNode('entities')
                    ->isRequired()
                    ->children()
                        ->append($productsNode)
                        ->append($draftsNode)
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }

    private function getDraftAndProductBaseNode($name)
    {
        $node = new ArrayNodeDefinition($name);
        $node
            ->children()
                ->scalarNode('filename')->isRequired()->info('Output filename of the CSV that will be generated')->end()
                ->integerNode('count')->min(1)->isRequired()->info('Number of items that will be generated')->end()
                ->integerNode('filled_attributes_count')->min(1)->isRequired()->info('Mean number of attributes that will be filled in the item')->end()
                ->integerNode('filled_attributes_standard_deviation')->min(1)->defaultValue(10)->info('Deviation of the mean number of attributes that will be filled in the item')->end()
                ->scalarNode('delimiter')->defaultValue(ProductGenerator::DEFAULT_DELIMITER)->info('Delimiter used in the CSV that will be generated')->end()
                ->arrayNode('mandatory_attributes')
                    ->prototype('scalar')->end()
                    ->defaultValue([])
                    ->info('Properties that will always be filled in with a random value')
                ->end()
                ->arrayNode('force_values')
                    ->prototype('scalar')->end()
                    ->defaultValue([])
                    ->info('Properties that, if they are filled in, will be filled in the given value')
                ->end()
                ->integerNode('start_index')->min(0)->defaultValue(0)->info('Start index of the identifiers')->end()
            ->end()
        ;

        return $node;
    }
}
