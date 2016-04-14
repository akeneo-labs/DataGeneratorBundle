<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Symfony\Component\Console\Helper\ProgressHelper;

/**
 * Fixture generator that will dispatch generation to specialized generator
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FixtureGenerator
{
    /** @var GeneratorRegistry */
    protected $registry;

    /** @var LocaleGenerator */
    protected $localeGenerator;

    /**
     * @param GeneratorRegistry $registry
     */
    public function __construct(GeneratorRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressHelper $progress, array $options = null)
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
            $generator = $this->registry->getGenerator($entity);
            if (null !== $generator) {
                $generatedValues = array_merge(
                    $generatedValues,
                    $generator->generate($globalConfig, $entityConfig, $progress, $generatedValues)
                );
            } else {
                echo sprintf("Generator for %s not found", $entity) . PHP_EOL;
            }
        }
    }
}
