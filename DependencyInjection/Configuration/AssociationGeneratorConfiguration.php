<?php

namespace Pim\Bundle\DataGeneratorBundle\DependencyInjection\Configuration;

use Pim\Bundle\DataGeneratorBundle\Generator\AssociationGenerator;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Product Generator configuration
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
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
                            ->scalarNode('delimiter')->defaultValue(AssociationGenerator::DEFAULT_DELIMITER)->info('Delimiter used in the CSV that will be generated')->end()
                            ->integerNode('associations_per_product')->min(0)->defaultValue(0)->info('Number of associations per product')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
