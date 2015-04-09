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
    const ATTRIBUTES_FILENAME = 'attributes.yml';

    const ATTRIBUTE_CODE_PREFIX = 'attr_';

    /** @var string */
    protected $attributesFile;

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

        $count = (int) $config['count'];
        $identifier = $config['identifier_attribute'];

        $this->faker = Faker\Factory::create();

        $this->attributes = [];

        $this->attributes[$identifier] = [
            'type'  => 'pim_catalog_identifier',
            'group' => $this->getRandomAttributeGroupCode()
        ];

        $forceAttributes = $config['force_attributes'];

        foreach ($forceAttributes as $forceAttribute) {
            list($code, $type) = explode('=', $forceAttribute);
            $this->attributes[trim($code)] = [
                'type'  => trim($type),
                'group' => $this->getRandomAttributeGroupCode()
            ];
        }

        for ($i = 0; $i < $count; $i++) {
            $attribute = [];

            $type = $this->getRandomAttributeType();
            $attribute['type'] = $type;
            $attribute['group'] = $this->getRandomAttributeGroupCode();
            $attribute['labels'] = $this->getLocalizedRandomLabels();
            $attribute['sortOrder'] = $this->faker->numberBetween(1, 10);
            $attribute['localizable'] = $this->faker->boolean();
            $attribute['scopable'] = $this->faker->boolean();

            if ('pim_catalog_metric' === $type) {
                $attribute = array_merge($attribute, $this->getMetricProperties());
            }

            if ('pim_catalog_image' === $type || 'pim_catalog_file' === $type) {
                $attribute = array_merge($attribute, $this->getMediaProperties());
            }

            $this->attributes[self::ATTRIBUTE_CODE_PREFIX.$i] = $attribute;
            $progress->advance();
        }

        $this->writeYamlFile(['attributes' => $this->attributes], $this->attributesFile);

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
     * Get a random non-identifier attribute type
     *
     * @return string
     */
    protected function getRandomAttributeType()
    {
        $attributeType = null;

        while ((null === $attributeType) || ('pim_catalog_identifier' === $attributeType)) {
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
     * Get all attribute groups
     *
     * @return array
     */
    protected function getAttributeGroupCodes()
    {
        if (null === $this->groupCodes) {
            $this->groupCodes = [];

            $groups = $this->groupRepository->findAll();
            foreach ($groups as $group) {
                $this->groupCodes[] = $group->getCode();
            }
        }

        return $this->groupCodes;
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
            "metricFamily"      => "Length",
            "defaultMetricUnit" => "METER"
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
            'allowedExtensions' => implode(
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
     * Write a YAML file
     *
     * @param array  $data
     * @param string $filename
     */
    protected function writeYamlFile(array $data, $filename)
    {
        $dumper = new Yaml\Dumper();
        $yamlData = $dumper->dump($data, 5, 0, true, true);

        file_put_contents($filename, $yamlData);
    }
}
