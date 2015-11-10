<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;

/**
 * Generate native YML file for attribute option useable as fixtures
 *
 * @author    Claire Fortin <claire.fortin@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionGenerator implements GeneratorInterface
{
    const ATTRIBUTE_OPTION_CODE_PREFIX = 'attr_opt_';

    const ATTRIBUTE_OPTIONS_FILENAME = 'attribute_options.csv';

    /** @var string */
    protected $outputFile;

    /** @var string */
    protected $delimiter;

    /** @var array */
    protected $locales;

    /** @var array */
    protected $attributeOptions = [];

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @ var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var array */
    protected $attributes;

    /** @var array */
    protected $selectAttributes;

    /** @var Faker\Generator */
    protected $faker;

    /**
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function __construct(
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->localeRepository = $localeRepository;
        $this->faker = Faker\Factory::create();
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress, array $options = null)
    {
        $countPerAttribute = (int) $config['count_per_attribute'];
        $this->delimiter   = $config['delimiter'];

        $this->attributeOptionsFile =  $outputDir.'/'.static::ATTRIBUTE_OPTIONS_FILENAME;

        foreach ($this->getSelectAttributes() as $attribute) {
            for ($i = 0; $i < $countPerAttribute; $i++) {
                $attributeOption = [];
                $attributeOption['attribute'] = $attribute->getCode();
                $attributeOption['code'] = static::ATTRIBUTE_OPTION_CODE_PREFIX . $attribute->getCode() . $i;
                $attributeOption['sort_order'] = $this->faker->numberBetween(1, $countPerAttribute);

                foreach ($this->getLocalizedRandomLabels() as $localeCode => $label) {
                    $attributeOption['label-'.$localeCode] = $label;
                }

                $this->attributeOptions[$attributeOption['code']] = $attributeOption;
            };
        }

        $headers = $this->getAllKeys($this->attributeOptions);

        $this->writeCsvFile($this->attributeOptions, $headers);

        return $this;
    }

    /**
     * Set attributes from Generator
     *
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Get localized random labels
     *
     * @return Locale[]
     */
    protected function getLocalizedRandomLabels()
    {
        $locales = $this->getLocales();
        $labels = [];

        foreach ($locales as $locale) {
            $labels[$locale->getCode()] = $this->faker->sentence(2);
        }

        return $labels;
    }

    /**
     * Get attributes that can have options
     *
     * @return Attributes[]
     *
     * @throw \LogicException
     */
    public function getSelectAttributes()
    {
        if (null === $this->selectAttributes) {
            $this->selectAttributes = [];

            if (null === $this->attributes) {
                throw new \LogicException("No attributes have been provided to the attributeOptionGenerator !");
            }

            foreach ($this->attributes as $attribute) {
                if ('pim_catalog_simpleselect' === $attribute->getAttributeType() ||
                    'pim_catalog_multiselect' === $attribute->getAttributeType()
                 ) {
                    $this->selectAttributes[$attribute->getCode()] = $attribute;
                }
            }
        }

        return $this->selectAttributes;
    }

    /**
     * Get active locales
     *
     * @return Locale[]
     */
    protected function getLocales()
    {
        if (null === $this->locales) {
            $this->locales = [];
            $locales = $this->localeRepository->findBy(['activated' => 1]);
            foreach ($locales as $locale) {
                $this->locales[$locale->getCode()] = $locale;
            }
        }

        return $this->locales;
    }

    /**
     * Write the CSV file from attributeOptions
     *
     * @param array $attributeOptions
     * @param array $headers
     */
    protected function writeCsvFile(array $attributeOptions, array $headers)
    {
        $csvFile = fopen($this->attributeOptionsFile, 'w');

        fputcsv($csvFile, $headers, $this->delimiter);
        $headersAsKeys = array_fill_keys($headers, "");

        foreach ($attributeOptions as $attributeOption) {
            $attributeOptionData = array_merge($headersAsKeys, $attributeOption);
            fputcsv($csvFile, $attributeOptionData, $this->delimiter);
        }
        fclose($csvFile);
    }

    /**
     * Get a set of all keys inside arrays
     *
     * @param array $items
     *
     * @return array
     */
    protected function getAllKeys(array $items)
    {
        $keys = [];

        foreach ($items as $item) {
            $keys = array_merge($keys, array_keys($item));
            $keys = array_unique($keys);
        }

        return $keys;
    }

}