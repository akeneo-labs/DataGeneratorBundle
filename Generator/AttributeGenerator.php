<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Symfony\Component\Console\Helper\ProgressBar;
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

    /** @var AttributeTypeRegistry */
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
     * @param AttributeTypeRegistry $typeRegistry
     */
    public function __construct(
        AttributeTypeRegistry $typeRegistry
    ) {
        $this->typeRegistry = $typeRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressBar $progress, array $options = [])
    {
        $this->locales         = $options['locales'];
        $this->attributeGroups = $options['attribute_groups'];

        $this->attributesFile = $globalConfig['output_dir'].'/'.self::ATTRIBUTES_FILENAME;
        $this->delimiter = $config['delimiter'];

        $count = (int) $config['count'];

        $localizableProbability = (float) $config['localizable_probability'];
        $scopableProbability    = (float) $config['scopable_probability'];
        $locScopableProbability = (float) $config['localizable_and_scopable_probability'];
        $gridFilterProbability  = (float) $config['useable_as_grid_filter_probability'];
        $minVariantAxes         = (int) $config['min_variant_axes'];
        $minVariantAttributes   = (int) $config['min_variant_attributes'];

        $identifier = $config['identifier_attribute'];

        $this->faker = Faker\Factory::create();
        if (isset($globalConfig['seed'])) {
            $this->faker->seed($globalConfig['seed']);
        }

        $this->attributes = [];

        $this->attributes[$identifier] = [
            'code'                   => $identifier,
            'type'                   => 'pim_catalog_identifier',
            'group'                  => $this->getRandomAttributeGroupCode(),
            'useable_as_grid_filter' => 1,
        ];

        $forceAttributes = $config['force_attributes'];

        foreach ($forceAttributes as $forceAttribute) {
            list($code, $type) = explode('=', $forceAttribute);
            $this->attributes[trim($code)] = [
                'code'  => trim($code),
                'type'  => trim($type),
                'group' => $this->getRandomAttributeGroupCode(),
            ];
        }

        for ($i = 0; $i < $count; $i++) {
            $attribute = [];
            $attribute['code'] = self::ATTRIBUTE_CODE_PREFIX.$i;

            $type = $this->getRandomAttributeType();
            $attribute['type'] = $type;
            $attribute['group'] = $this->getRandomAttributeGroupCode();

            if ($type == AttributeTypes::OPTION_SIMPLE_SELECT && $minVariantAxes > 0) {
                // Configure a minimum set of non localizable and non scopable select axes for variant groups.
                $attribute['localizable'] = 0;
                $attribute['scopable'] = 0;
                $minVariantAxes--;
            } elseif ($type == AttributeTypes::TEXT && $minVariantAttributes > 0) {
                // Configure a minimum set of non localizable and non scopable text attributes for variant groups.
                $attribute['localizable'] = 0;
                $attribute['scopable'] = 0;
                $minVariantAttributes--;
            } elseif ($this->faker->boolean($locScopableProbability)) {
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

        foreach ($this->attributes as $code => $attribute) {
            foreach ($this->getLocalizedRandomLabels($type) as $localeCode => $label) {
                $this->attributes[$code]['label-' . $localeCode] = $label;
            }

            if (!isset($attribute['useable_as_grid_filter'])) {
                $useable = (int) $this->faker->boolean($gridFilterProbability);
                $this->attributes[$code]['useable_as_grid_filter'] = $useable;
            }
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
    public function getAttributes()
    {
        $attributeObjects = [];

        foreach ($this->attributes as $code => $attribute) {
            $attributeObject = new Attribute();

            $attributeObject->setCode($code);
            $attributeObject->setAttributeType($attribute['type']);

            if (isset($attribute['localizable'])) {
                $attributeObject->setLocalizable($attribute['localizable']);
            }
            if (isset($attribute['scopable'])) {
                $attributeObject->setScopable($attribute['scopable']);
            }
            $attributeObject->setAttributeType($attribute['type']);
            $attributeObject->setUseableAsGridFilter($attribute['useable_as_grid_filter']);

            $attributeObjects[$code] = $attributeObject;
        }

        return $attributeObjects;
    }

    /**
     * Return the generated media attribute codes
     *
     * @return array
     */
    public function getMediaAttributeCodes()
    {
        $codes = [];

        foreach ($this->attributes as $code => $attribute) {
            if (in_array($attribute['type'], [AttributeTypes::IMAGE, AttributeTypes::FILE])) {
                $codes[] = $code;
            }
        }

        return $codes;
    }

    /**
     * Get a random non-identifier attribute type
     *
     * @return string
     */
    protected function getRandomAttributeType()
    {
        $attributeType = null;
        $typesToAvoid = [
            'pim_catalog_identifier',
            'pim_reference_data_multiselect',
            'pim_reference_data_simpleselect',
            'pim_assets_collection',
        ];

        while ((null === $attributeType) || in_array($attributeType, $typesToAvoid)) {
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
     * @param string $type
     *
     * @return array
     */
    protected function getLocalizedRandomLabels($type)
    {
        $labels = [];

        foreach ($this->locales as $locale) {
            $labels[$locale->getCode()] = sprintf("%s %s", $type, implode(' ', $this->faker->words(2)));
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
            "metric_family"       => "Length",
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
