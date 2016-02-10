<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\GroupTranslation;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\GroupTypeInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
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
    protected $attributes;

    /** @var GroupTypeInterface */
    protected $variantGroupType;

    /** @var Faker\Generator */
    protected $faker;

    /**
     * @param AttributeInterface[] $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @param LocaleInterface[] $locales
     */
    public function setLocales(array $locales)
    {
        $this->locales = $locales;
    }

    /**
     * @param GroupTypeInterface[] $groupTypes
     *
     * @return VariantGroupGenerator
     */
    public function setGroupTypes(array $groupTypes)
    {
        foreach ($groupTypes as $groupType) {
            if ($groupType->getCode() == 'VARIANT') {
                $this->variantGroupType = $groupType;

                return $this;
            }
        }

        throw new Exception('There is no VARIANT group. \
            Please add "group_types: ~" into your fixtures configuration file.');
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress, array $options = null)
    {
        $this->faker = Faker\Factory::create();

        $variantGroups = [];
        $data          = [];

        for ($i = 0; $i < $config['count']; $i++) {
            $variantGroup = $this->generateVariantGroup($config);
            $variantGroups[] = $variantGroup;
            $data[] = $this->normalizeVariantGroup($variantGroup);

            $progress->advance();
        }

        if (count($variantGroups) > 0) {
            $this->writeCsvFIle($data, array_keys($data[0]), $outputDir);
        }

        return $variantGroups;
    }

    /**
     * @param array $config
     *
     * @return GroupInterface
     */
    protected function generateVariantGroup($config)
    {
        $group = new Group();
        $group->setType($this->variantGroupType);
        $group->setCode($this->faker->word());

        $axisAttributes = [];
        for ($i = 0; $i < $config['axis_count']; $i++) {
            $axisAttributes[] = $this->faker->randomElement($this->attributes);
        }

        $group->setAxisAttributes($axisAttributes);

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
        $axis = implode(',', array_map(function ($attribute) {
            return $attribute->getCode();
        }, $variantGroup->getAxisAttributes()->toArray()));

        $result = [
            'code' => $variantGroup->getCode(),
            'axis' => $axis,
            'type' => $variantGroup->getType()->getCode()
        ];

        /** @var GroupTranslation $translation */
        foreach ($variantGroup->getTranslations() as $translation) {
            $result[sprintf('label-%s', $translation->getLocale())] = $translation->getLabel();
        }

        return $result;
    }

    /**
     * Write the CSV file from variant groups and headers
     *
     * @param array  $variantGroups
     * @param array  $headers
     * @param string $outputDir
     */
    protected function writeCsvFIle(array $variantGroups, array $headers, $outputDir)
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
