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
                        ->arrayNode('channels')
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('code')->isRequired()->end()
                                    ->scalarNode('label')->isRequired()->end()
                                    ->arrayNode('locales')
                                        ->isRequired()
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->arrayNode('currencies')
                                        ->isRequired()
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->scalarNode('color')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('users')
                            ->defaultValue([
                                "admin" => [
                                    "username"  => "admin",
                                    "password"  => "admin",
                                    "email"     => "admin@example.com",
                                    "firstname" => "Peter",
                                    "lastname"  => "Doe",
                                    "roles"     => [ "ROLE_ADMINISTRATOR" ],
                                    "groups"    => [ "IT support" ],
                                    "enable" => true
                                ]
                            ])
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('username')->isRequired()->end()
                                    ->scalarNode('password')->isRequired()->end()
                                    ->scalarNode('email')->isRequired()->end()
                                    ->scalarNode('firstname')->isRequired()->end()
                                    ->scalarNode('lastname')->isRequired()->end()
                                    ->scalarNode('catalog_locale')->end()
                                    ->scalarNode('catalog_scope')->end()
                                    ->scalarNode('default_tree')->end()
                                    ->arrayNode('roles')
                                        ->isRequired()
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->arrayNode('groups')
                                        ->isRequired()
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->booleanNode('enable')->defaultFalse()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('user_groups')
                            ->useAttributeAsKey('name')
                            ->defaultValue([
                                "it_support" => [
                                    "name" => "IT support"
                                ]
                            ])
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('name')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('user_roles')
                            ->useAttributeAsKey('name')
                            ->defaultValue([
                                "ROLE_ADMINISTRATOR" => [
                                    "label" => "Administrator"
                                ]
                            ])
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('name')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('jobs')
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('connector')->isRequired()->end()
                                    ->scalarNode('alias')->isRequired()->end()
                                    ->scalarNode('label')->isRequired()->end()
                                    ->scalarNode('type')->isRequired()->end()
                                    ->arrayNode('configuration')
                                        ->isRequired()
                                        ->prototype("scalar")->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('categories')
                            ->children()
                                ->integerNode('count')->min(1)->isRequired()->end()
                                ->integerNode('levels')->min(1)->defaultValue(1)->end()
                                ->scalarNode('delimiter')->defaultValue(';')->end()
                            ->end()
                        ->end()
                        ->arrayNode('attribute_groups')
                            ->children()
                                ->integerNode('count')->min(1)->isRequired()->end()
                            ->end()
                        ->end()
                        ->arrayNode('attributes')
                            ->children()
                                ->integerNode('count')->min(1)->isRequired()->end()
                                ->scalarNode('identifier_attribute')->isRequired()->end()
                                ->scalarNode('delimiter')->defaultValue(';')->end()
                                ->integerNode('localizable_probability')->defaultValue(50)->end()
                                ->integerNode('scopable_probability')->defaultValue(50)->end()
                                ->integerNode('localizable_and_scopable_probability')->defaultValue(50)->end()
                                ->arrayNode('force_attributes')
                                    ->prototype('scalar')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('attribute_options')
                            ->children()
                                ->integerNode('count_per_attribute')->min(1)->isRequired()->end()
                                ->scalarNode('delimiter')->defaultValue(';')->end()
                            ->end()
                        ->end()
                        ->arrayNode('families')
                            ->children()
                                ->integerNode('count')->min(1)->isRequired()->end()
                                ->integerNode('attributes_count')->min(1)->defaultValue(30)->end()
                                ->scalarNode('identifier_attribute')->isRequired()->end()
                                ->scalarNode('label_attribute')->isRequired()->end()
                                ->integerNode('requirements_count')->min(1)->defaultValue(5)->end()
                                ->scalarNode('delimiter')->defaultValue(';')->end()
                            ->end()
                        ->end()
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
                            ->end()
                        ->end()
                        ->arrayNode('asset_categories')->end()
                        ->arrayNode('asset_category_accesses')->end()
                        ->arrayNode('attribute_groups_accesses')->end()
                        ->arrayNode('job_profiles_accesses')->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
