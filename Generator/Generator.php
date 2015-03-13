<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Symfony\Component\Console\Helper\ProgressHelper;

/**
 * Generic generator that will dispatch generation to specialized generator
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Generator implements GeneratorInterface
{
    /** @var AttributeGenerator */
    protected $attributeGenerator;

    /** @var ProductGenerator */
    protected $productGenerator;

    /**
     * @param AttributeGenerator $attributeGenerator
     * @param ProductGenerator   $productGenerator
     */
    //public function __construct(AttributeGenerator $attributeGenerator, ProductGenerator $productGenerator)
    public function __construct(ProductGenerator $productGenerator)
    {
//        $this->attributeGenerator = $attributeGenerator;
        $this->productGenerator   = $productGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress)
    {
        $generatedAttributes = null;

        if (isset($config['entities']['attribute'])) {
            $attributeConfig = $config['entities']['attribute'];
//            $this->attributeGenerator->generate($attributeConfig, $outputDir, $progress);
//            $generatedAttributes = $this->attributeGenerator->getGeneratedAttributes();
        }

        if (isset($config['entities']['product'])) {
            $productConfig = $config['entities']['product'];
            if (null !== $generatedAttributes) {
                $this->productGenerator->setExtraAttributes($generatedAttributes);
            }
            $this->productGenerator->generate($productConfig, $outputDir, $progress);
        }

    }
}
