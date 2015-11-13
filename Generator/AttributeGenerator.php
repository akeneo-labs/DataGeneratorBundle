<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;

/**
 * Generate native YML file for attributes useable as fixtures
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGenerator implements GeneratorInterface
{
    const ATTRIBUTES_FILENAME = 'attributes.csv';

    const ATTRIBUTE_CODE_PREFIX = 'attr_';

    /** @var string */
    protected $attributesFile;

    /** @var array */
    protected $attributeGroups;

    /** @var array */
    protected $attributeGroupCodes;

    /** @var array */
    protected $locales;

    /** @var AttributeGroupRepositoryInterface */
    protected $groupRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @ var AttributeTypeRegistry */
    protected $typeRegistry;

    /** @var array */
    protected $groupCodes;

    /** @var Faker\Generator */
    protected $faker;

    /** @var array */
    protected $attributes;

    /** @var string */
    protected $delimiter;

    /**
     * @param AttributeGroupRepositoryInterface $groupRepository
     * @param LocaleRepositoryInterface         $localeRepository
     * @param AttributeTypeRegistry             $typeRegistry
     */
    public function __construct(
        AttributeGroupRepositoryInterface $groupRepository,
        LocaleRepositoryInterface $localeRepository,
        AttributeTypeRegistry $typeRegistry
    ) {
        $this->groupRepository  = $groupRepository;
        $this->localeRepository = $localeRepository;
        $this->typeRegistry     = $typeRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress, array $options = null)
    {
        $this->attributesFile = $outputDir.'/'.self::ATTRIBUTES_FILENAME;
        $this->delimiter = $config['delimiter'];

        $count = (int) $config['count'];

        $localizableProbability = (int) $config['localizable_probability'];
        $scopableProbability = (int) $config['scopable_probability'];
        $locScopableProbability = (int) $config['localizable_and_scopable_probability'];

        $identifier = $config['identifier_attribute'];

        $this->faker = Faker\Factory::create();

        $this->attributes = [];

        $this->attributes[$identifier] = [
            'code'  => $identifier,
            'type'  => 'pim_catalog_identifier',
            'group' => $this->getRandomAttributeGroupCode()
        ];

        $forceAttributes = $config['force_attributes'];

        foreach ($forceAttributes as $forceAttribute) {
            list($code, $type) = explode('=', $forceAttribute);
            $this->attributes[trim($code)] = [
                'code'  => trim($code),
                'type'  => trim($type),
                'group' => $this->getRandomAttributeGroupCode()
            ];
        }

        for ($i = 0; $i < $count; $i++) {
            $attribute = [];
            $attribute['code'] = self::ATTRIBUTE_CODE_PREFIX.$i;

            $type = $this->getRandomAttributeType();
            $attribute['type'] = $type;
            $attribute['group'] = $this->getRandomAttributeGroupCode();

            foreach($this->getLocalizedRandomLabels() as $localeCode => $label) {
                $attribute['label-'.$localeCode] = $label;
            }

            if ($this->faker->boolean($locScopableProbability)) {
                $attribute['localizable'] = 1;
                $attribute['scopable'] = 1;
            } else {
                $attribute['localizable'] = (int) $this->faker->boolean($localizableProbability);
                $attribute['scopable'] = (int) $this->faker->boolean($scopableProbability);
            }

            if ('pim_catalog_metric' === $type) {
                $attribute = array_merge($attribute, $this->getMetricProperties());
            }

            if ('pim_catalog_image' === $type || 'pim_catalog_file' === $type) {
                $attribute = array_merge($attribute, $this->getMediaProperties());
            }

            $this->attributes[$attribute['code']] = $attribute;
            $progress->advance();
        }
        $headers = $this->getAllKeys($this->attributes);

        $this->writeCsvFile($this->attributes, $headers);

        return $this;
    }

    /**
     * Return the generated attributes as Attribute object
     *
     * @return array
     */
    public function getAttributeObjects()
    {
        $attributeObjects = [];

        foreach ($this->attributes as $code => $attribute) {
            $attributeObject = new Attribute();

            $attributeObject->setCode($code);
            $attributeObject->setAttributeType($attribute['type']);

            if (isset($attribute['localizable'])) {
                $attributeObject->setLocalizable($attribute['localizable']);
            }
            if (isset($attribute['localizable'])) {
                $attributeObject->setScopable($attribute['scopable']);
            }

            $attributeObjects[$code] = $attributeObject;
        }

        return $attributeObjects;
    }

    /**
     * Set attribute groups.
     *
     * @param array $attributeGroups
     */
    public function setAttributeGroups(array $attributeGroups)
    {
        $this->attributeGroups = $attributeGroups;
    }

    /**
     * Get a random non-identifier attribute type
     *
     * @return string
     */
    protected function getRandomAttributeType()
    {
        $attributeType = null;

        while (
            (null === $attributeType) ||
            ('pim_catalog_identifier' === $attributeType) ||
            ('pim_reference_data_multiselect' === $attributeType) ||
            ('pim_reference_data_simpleselect' === $attributeType)
        ) {
            $attributeType = $this->faker->randomElement($this->getAttributeTypeCodes());
        }

        return $attributeType;
    }

    /**
     * Get an array of attribute type.
     * FIXME: Get them from the PIM.
     *
     * @return array
     */
    protected function getAttributeTypeCodes()
    {
        return $this->typeRegistry->getAliases();
    }


    /**
     * Get a random attribute group code
     *
     * @return string
     */
    protected function getRandomAttributeGroupCode()
    {
        return $this->faker->randomElement($this->getAttributeGroupCodes());
    }

    /**
     * Get all generated attribute groups.
     *
     * @return array
     */
    protected function getAttributeGroupCodes()
    {
        if (null === $this->attributeGroupCodes) {
            $this->attributeGroupCodes = [];
            foreach (array_keys($this->attributeGroups) as $code) {
                $this->attributeGroupCodes[] = $code;
            }
        }

        return $this->attributeGroupCodes;
    }

    /**
     * Get localized random labels
     *
     * @return array
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
     * Provide metric specific properties
     *
     * @return array
     */
    protected function getMetricProperties()
    {
        return [
            "metric_family"      => "Length",
            "default_metric_unit" => "METER"
        ];
    }

    /**
     * Provide media specific properties
     *
     * @return array
     */
    protected function getMediaProperties()
    {
        return [
            'allowed_extensions' => implode(
                ',',
                $this->faker->randomElements(['png', 'jpg', 'pdf'], 2)
            )
        ];
    }

    /**
     * Get active locales
     *
     * @return array
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
     * Write the CSV file from attributes
     *
     * @param array $attributes
     * @param array $headers
     */
    protected function writeCsvFile(array $attributes, array $headers)
    {
        $csvFile = fopen($this->attributesFile, 'w');

        fputcsv($csvFile, $headers, $this->delimiter);
        $headersAsKeys = array_fill_keys($headers, "");

        foreach ($attributes as $attribute) {
            $attributeData = array_merge($headersAsKeys, $attribute);
            fputcsv($csvFile, $attributeData, $this->delimiter);
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
