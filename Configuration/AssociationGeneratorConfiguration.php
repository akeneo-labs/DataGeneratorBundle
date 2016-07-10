<?php

namespace Pim\Bundle\DataGeneratorBundle\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Association Generator configuration
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationGeneratorConfiguration implements ConfigurationInterface
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
                ->scalarNode('output_dir')->isRequired()->cannotBeEmpty()->info('Directory where files will be generated')->end()
                ->integerNode('seed')->defaultValue(null)->info('Seed used to generate random values')->end()
                ->arrayNode('entities')
                    ->isRequired()
                    ->children()
                        ->arrayNode('associations')
                        ->children()
                            ->scalarNode('filename')->isRequired()->info('Output filename of the CSV that will be generated')->end()
                            ->scalarNode('delimiter')->defaultValue(';')->info('Delimiter used in the CSV that will be generated')->end()
                            ->integerNode('product_associations_per_product')->min(0)->defaultValue(0)->info('Number of product associations per product')->end()
                            ->integerNode('group_associations_per_product')->min(0)->defaultValue(0)->info('Number of group associations per product')->end()
                            ->integerNode('products_to_process_limit')->min(0)->info('Number limit of product to process')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
