<?php

namespace Pim\Bundle\DataGeneratorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass that register generators
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterGeneratorsPass implements CompilerPassInterface
{
    const GENERATOR_REGISTRY = 'pim_data_generator.generator.registry';

    const GENERATOR_TAG = 'pim_data_generator.generator';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(self::GENERATOR_REGISTRY);
        $ids = array_keys($container->findTaggedServiceIds(self::GENERATOR_TAG));

        foreach ($ids as $id) {
            $definition->addMethodCall('register', [new Reference($id)]);
        }
    }
}
