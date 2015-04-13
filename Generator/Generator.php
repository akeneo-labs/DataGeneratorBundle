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

    /** @var FamilyGenerator */
    protected $familyGenerator;

    /** @var ProductGenerator */
    protected $productGenerator;

    /** @var CategoryGenerator */
    protected $categoryGenerator;

    /** @var AttributeGroupGenerator */
    protected $attrGroupGenerator;

    /**
     * @param AttributeGenerator      $attributeGenerator
     * @param FamilyGenerator         $familyGenerator
     * @param ProductGenerator        $productGenerator
     * @param CategoryGenerator       $categoryGenerator
     * @param AttributeGroupGenerator $attrGroupGenerator
     */
    public function __construct(
        AttributeGenerator $attributeGenerator,
        FamilyGenerator $familyGenerator,
        ProductGenerator $productGenerator,
        CategoryGenerator $categoryGenerator,
        AttributeGroupGenerator $attrGroupGenerator
    ) {
        $this->attributeGenerator = $attributeGenerator;
        $this->familyGenerator    = $familyGenerator;
        $this->productGenerator   = $productGenerator;
        $this->categoryGenerator  = $categoryGenerator;
        $this->attrGroupGenerator = $attrGroupGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress, array $options = null)
    {
        $generatedAttrGroups = [];
        $generatedAttributes = [];

        if (isset($config['entities']['product']) && count($config['entities']) > 1) {
            throw new \LogicException(
                'Products can be generated at the same time of other entities.'.
                'Please generate attributes and families, import them, then generate products'
            );
        }

        if (isset($config['entities']['category'])) {
            $categoryConfig = $config['entities']['category'];
            $this->categoryGenerator->generate($categoryConfig, $outputDir, $progress);
        }

        if (isset($config['entities']['attribute_group'])) {
            $attributeGroupConfig = $config['entities']['attribute_group'];
            $this->attrGroupGenerator->generate($attributeGroupConfig, $outputDir, $progress);
            $generatedAttrGroups = $this->attrGroupGenerator->getAttributeGroupObjects();
        }

        if (isset($config['entities']['attribute'])) {
            $attributeConfig = $config['entities']['attribute'];
            $this->attributeGenerator->setAttributeGroups($generatedAttrGroups);
            $this->attributeGenerator->generate($attributeConfig, $outputDir, $progress);
            $generatedAttributes = $this->attributeGenerator->getAttributeObjects();
        }

        if (isset($config['entities']['family'])) {
            $familyConfig = $config['entities']['family'];
            $this->familyGenerator->setAttributes($generatedAttributes);
            $this->familyGenerator->generate($familyConfig, $outputDir, $progress);
        }

        if (isset($config['entities']['product'])) {
            $productConfig = $config['entities']['product'];
            if (!empty($generatedAttributes)) {
                $this->productGenerator->setExtraAttributes($generatedAttributes);
            }
            $this->productGenerator->generate($productConfig, $outputDir, $progress);
        }

        $progress->finish();
    }
}
