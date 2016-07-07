<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Fixture generator that will dispatch generation to specialized generator
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChainedGenerator
{
    /** @var GeneratorRegistry */
    protected $registry;

    /**
     * @param GeneratorRegistry $registry
     */
    public function __construct(GeneratorRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Generates a set of files
     *
     * @param array       $globalConfig
     * @param ProgressBar $progress
     *
     * @throws \Exception
     */
    public function generate(array $globalConfig, ProgressBar $progress)
    {
        $entitiesConfig = $globalConfig['entities'];
        unset($globalConfig['entities']);

        // Insert axes count and attributes count for attributes generation to have consistency
        if (isset($entitiesConfig['attributes'])) {
            if (isset($entitiesConfig['variant_groups']['axes_count'])) {
                $variantGroupAxisCount = $entitiesConfig['variant_groups']['axes_count'];
                $entitiesConfig['attributes']['min_variant_axes'] = $variantGroupAxisCount;
            }
            if (isset($entitiesConfig['variant_groups']['attributes_count'])) {
                $variantGroupAttributesCount = $entitiesConfig['variant_groups']['attributes_count'];
                $entitiesConfig['attributes']['min_variant_attributes'] = $variantGroupAttributesCount;
            }
        }

        $generatedValues = [];
        foreach ($entitiesConfig as $entity => $entityConfig) {
            $progress->setMessage(sprintf('Generating %s...', $entity));
            $generator = $this->registry->getGenerator($entity);
            var_dump(get_class($generator));
            if (null !== $generator) {
                $generatedValues = array_merge(
                    $generatedValues,
                    $generator->generate($globalConfig, $entityConfig, $progress, $generatedValues)
                );
            } else {
                throw new \Exception(sprintf('Generator for "%s" not found', $entity));
            }
        }
        $progress->setMessage('');
    }
}
