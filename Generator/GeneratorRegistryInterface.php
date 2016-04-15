<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

/**
 * Interface for Generators registries.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GeneratorRegistryInterface
{
    /**
     * Register a new Generator
     *
     * @param GeneratorInterface $generator
     *
     * @return GeneratorRegistryInterface
     */
    public function register(GeneratorInterface $generator);

    /**
     * Get a generator supported by type
     *
     * @param string $type
     *
     * @return GeneratorInterface|null
     */
    public function getGenerator($type);
}
