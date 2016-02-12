<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\GroupTranslation;
use Pim\Bundle\CatalogBundle\Entity\ProductTemplate;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\GroupTypeInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Helper\ProgressHelper;

/**
 * Generate native CSV file for variant groups.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupGenerator implements GeneratorInterface
{
    const VARIANT_GROUPS_FILENAME = 'variant_groups.csv';

    /** @var LocaleInterface[] */
    protected $locales;

    /** @var AttributeInterface[] */
    protected $availableAxes;

    /** @var AttributeInterface[] */
    protected $availableAttributes;

    /** @var GroupTypeInterface */
    protected $variantGroupType;

    /** @var Faker\Generator */
    protected $faker;

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress, array $options = [])
    {
        $this->setAttributes($options['attributes']);
        $this->setGroupTypes($options['group_types']);
        $this->locales = $options['locales'];

        $this->faker = Faker\Factory::create();

        $variantGroups = [];
        $data          = [];

        for ($i = 0; $i < $config['count']; $i++) {
            $variantGroup = $this->generateVariantGroup($config, $i);
            $variantGroups[] = $variantGroup;
            $data[] = $this->normalizeVariantGroup($variantGroup);

            $progress->advance();
        }

        if (count($variantGroups) > 0) {
            $this->writeCsvFile($data, $this->getHeader($data), $outputDir);
        }

        return $variantGroups;
    }

    /**
     * @param array $config
     * @param int   $index
     *
     * @return GroupInterface
     */
    protected function generateVariantGroup($config, $index)
    {
        $group = new Group();
        $group->setType($this->variantGroupType);
        $group->setCode(sprintf('variant_group_%s', $index));

        $group->setAxisAttributes($this->getAxes($config['axes_count']));
        $group->setProductTemplate($this->getProductTemplate($config['attributes_count']));

        foreach ($this->locales as $locale) {
            $translation = new GroupTranslation();
            $translation->setLabel($this->faker->word());
            $translation->setLocale($locale->getCode());
            $group->addTranslation($translation);
        }

        return $group;
    }

    /**
     * @param GroupInterface $variantGroup
     *
     * @return array
     */
    protected function normalizeVariantGroup(GroupInterface $variantGroup)
    {
        $axes = implode(',', array_map(function ($attribute) {
            return $attribute->getCode();
        }, $variantGroup->getAxisAttributes()->toArray()));

        $result = [
            'code' => $variantGroup->getCode(),
            'axis' => $axes,
            'type' => $variantGroup->getType()->getCode()
        ];

        /** @var GroupTranslation $translation */
        foreach ($variantGroup->getTranslations() as $translation) {
            $result[sprintf('label-%s', $translation->getLocale())] = $translation->getLabel();
        }

        /**
         * @var string                $attributeCode
         * @var ProductValueInterface $productValue
         */
        foreach ($variantGroup->getProductTemplate()->getValuesData() as $attributeCode => $productValue) {
            $result[$attributeCode] = $productValue->getText();
        }

        return $result;
    }

    /**
     * Return a random set of axes for a variant group.
     *
     * @param int $count
     *
     * @return AttributeInterface[]
     */
    protected function getAxes($count)
    {
        $attributesFaker = Faker\Factory::create();
        $axes            = [];

        for ($i = 0; $i < $count; $i++) {
            try {
                $axis = $attributesFaker->unique()->randomElement($this->availableAxes);
            } catch (\OverflowException $e) {
                throw new Exception(sprintf(
                    'There is only %s attributes available for variant group axes, %s needed.',
                    count($this->availableAxes),
                    $count
                ));
            }
            $axes[] = $axis;
        }

        return $axes;
    }

    /**
     * Returns a product template containing product values for a variant group.
     *
     * @param int $count
     *
     * @return ProductTemplate
     */
    protected function getProductTemplate($count)
    {
        $attributesFaker = Faker\Factory::create();
        $valuesData      = [];

        for ($i = 0; $i < $count; $i++) {
            try {
                $attribute = $attributesFaker->unique($i == 0)->randomElement($this->availableAttributes);
            } catch (\OverflowException $e) {
                throw new Exception(sprintf(
                    'There is only %s attributes available for variant group attribute, %s needed.',
                    count($this->availableAttributes),
                    $count
                ));
            }
            $value = new ProductValue();
            $value->setAttribute($attribute);
            $value->setText(implode(' ', $this->faker->words(3)));
            $valuesData[$attribute->getCode()] = $value;
        }
        $productTemplate = new ProductTemplate();
        $productTemplate->setValuesData($valuesData);

        return $productTemplate;
    }

    /**
     * Configure the 2 sets of available attributes (non localizable and non scopable):
     * - the available attributes to define variant group axes (only selects)
     * - the available attributes to define variant group attributes (only texts)
     *
     * @param AttributeInterface[] $attributes
     */
    protected function setAttributes(array $attributes)
    {
        $this->availableAxes = array_filter($attributes, function ($attribute) {
            /** @var $attribute AttributeInterface */
            return in_array($attribute->getAttributeType(), [
                AttributeTypes::OPTION_SIMPLE_SELECT,
                AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT
            ]) && !$attribute->isLocalizable() && !$attribute->isScopable();
        });

        $this->availableAttributes = array_filter($attributes, function ($attribute) {
            /** @var $attribute AttributeInterface */
            return (($attribute->getAttributeType() == AttributeTypes::TEXT)
                && !$attribute->isLocalizable()
                && !$attribute->isScopable()
            );
        });
    }

    /**
     * @param GroupTypeInterface[] $groupTypes
     *
     * @return VariantGroupGenerator
     */
    protected function setGroupTypes(array $groupTypes)
    {
        foreach ($groupTypes as $groupType) {
            if ($groupType->getCode() == 'VARIANT') {
                $this->variantGroupType = $groupType;

                return $this;
            }
        }

        throw new Exception('There is no VARIANT group. ' .
            'Please add "group_types: ~" into your fixtures configuration file.');
    }

    /**
     * Get the header of the CSV.
     *
     * @param array $variantGroups
     *
     * @return array
     */
    protected function getHeader(array $variantGroups)
    {
        $header = [];

        foreach($variantGroups as $variantGroup) {
            foreach ($variantGroup as $key => $value) {
                if (!in_array($key, $header)) {
                    $header[] = $key;
                }
            }
        }

        return $header;
    }

    /**
     * Write the CSV file from variant groups and headers
     *
     * @param array  $variantGroups
     * @param array  $headers
     * @param string $outputDir
     */
    protected function writeCsvFile(array $variantGroups, array $headers, $outputDir)
    {
        $csvFile = fopen($outputDir.'/'.self::VARIANT_GROUPS_FILENAME, 'w');

        fputcsv($csvFile, $headers, ';');
        $headersAsKeys = array_fill_keys($headers, "");

        foreach ($variantGroups as $variantGroup) {
            $productData = array_merge($headersAsKeys, $variantGroup);
            fputcsv($csvFile, $productData, ';');
        }
        fclose($csvFile);
    }
}
