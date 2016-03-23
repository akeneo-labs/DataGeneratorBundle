<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;

/**
 * Generate native YML file for family useable as fixtures
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyGenerator implements GeneratorInterface
{
    const FAMILIES_FILENAME = 'families.csv';

    const FAMILY_CODE_PREFIX = 'fam_';

    const ATTRIBUTE_DELIMITER = ',';

    /** @var string */
    protected $familiesFile;

    /** @var string */
    protected $delimiter;

    /** @var string */
    protected $identifierAttribute;

    /** @var string */
    protected $labelAttribute;

    /** @var array */
    protected $locales;

    /** @var array */
    protected $channels;

    /** @var Faker\Generator */
    protected $faker;

    /** @var array */
    protected $families;

    /** @var array */
    protected $attributes;

    /** @var array */
    protected $filteredAttrCodes;

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressHelper $progress, array $options = [])
    {
        $this->locales    = $options['locales'];
        $this->attributes = $options['attributes'];
        $this->channels   = $options['channels'];

        $this->familiesFile = $globalConfig['output_dir'].'/'.self::FAMILIES_FILENAME;

        $this->delimiter = $config['delimiter'];

        $count = (int) $config['count'];
        $attributesCount = (int) $config['attributes_count'] - 1;
        $requirementsCount = (int) $config['requirements_count'] - 1;
        $this->identifierAttribute = $config['identifier_attribute'];
        $this->labelAttribute      = $config['label_attribute'];

        $this->faker = Faker\Factory::create();
        if (isset($globalConfig['seed'])) {
            $this->faker->seed($globalConfig['seed']);
        }

        $families = [];

        for ($i = 0; $i < $count; $i++) {
            $family = [];

            $family['code'] =self::FAMILY_CODE_PREFIX.$i;

            foreach ($this->getLocalizedRandomLabels() as $localeCode => $label) {
                $family['label-'.$localeCode] = $label;
            }

            $family['attribute_as_label'] = $this->labelAttribute;

            $attributes = $this->faker->randomElements($this->getAttributeCodes(), $attributesCount);
            $attributes = array_unique(array_merge([$this->identifierAttribute, $this->labelAttribute], $attributes));
            $nonMediaAttributeCodes = array_diff($attributes, $options['media_attribute_codes']);

            $family['attributes'] = implode(static::ATTRIBUTE_DELIMITER, $attributes);

            foreach ($this->channels as $channel) {
                // non media attributes can't be set to required to avoid to have to generate for complete products
                $attributeReqs = $this->faker->randomElements($nonMediaAttributeCodes, $requirementsCount);
                $attributeReqs = array_merge([$this->identifierAttribute], $attributeReqs);

                $family['requirements-'.$channel->getCode()] = implode(static::ATTRIBUTE_DELIMITER, $attributeReqs);
            }

            $families[$family['code']] = $family;
            $progress->advance();
        }

        $this->families = $families;

        $headers = $this->getAllKeys($this->families);

        $this->writeCsvFile($this->families, $headers);

        return $this;
    }

    /**
     * Return the generated families as Family object
     *
     * @return array
     */
    public function getFamilyObjects()
    {
        $familyObjects = [];

        foreach ($this->families as $code => $family) {
            $familyObject = new Family();

            $familyObject->setCode($code);

            $familyObjects[] = $familyObject;
        }

        return $familyObjects;
    }

    /**
     * Get localized random labels
     *
     * @return array
     */
    protected function getLocalizedRandomLabels()
    {
        $labels = [];

        foreach ($this->locales as $locale) {
            $labels[$locale->getCode()] = $this->faker->sentence(2);
        }

        return $labels;
    }

    /**
     * Get attributes codes
     *
     * @return array
     */
    protected function getAttributeCodes()
    {
        if (null === $this->filteredAttrCodes) {
            $this->filteredAttrCodes = [];
            foreach (array_keys($this->attributes) as $code) {
                if ($code !== $this->identifierAttribute && $code !== $this->labelAttribute) {
                    $this->filteredAttrCodes[] = $code;
                }
            }
        }

        return $this->filteredAttrCodes;
    }

    /**
     * Write the CSV file from families
     *
     * @param array $families
     * @param array $headers
     */
    protected function writeCsvFile(array $families, array $headers)
    {
        $csvFile = fopen($this->familiesFile, 'w');

        fputcsv($csvFile, $headers, $this->delimiter);
        $headersAsKeys = array_fill_keys($headers, "");

        foreach ($families as $family) {
            $familyData = array_merge($headersAsKeys, $family);
            fputcsv($csvFile, $familyData, $this->delimiter);
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
