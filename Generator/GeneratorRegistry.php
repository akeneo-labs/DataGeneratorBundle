<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GeneratorRegistry
{
    /** @var GeneratorInterface[] */
    protected $generators = [];

    /**
     * {@inheritdoc}
     */
    public function register(GeneratorInterface $generator)
    {
        $this->generators[] = $generator;

        return $this;
    }

    /**
     * Get a generator supported by type
     *
     * @param string $type
     *
     * @return GeneratorInterface|null
     */
    public function getGenerator($type)
    {
        foreach ($this->generators as $generator) {
            if ($generator->supports($type)) {
                return $generator;
            }
        }

        return null;
    }
}
