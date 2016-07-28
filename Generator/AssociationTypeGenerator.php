<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\AssociationTypeTranslation;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Symfony\Component\Console\Helper\ProgressHelper;

/**
 * Generate native CSV file for association types.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeGenerator implements GeneratorInterface
{
    const ASSOCIATION_TYPES_FILENAME = 'association_types.csv';

    /** @var Locale[] */
    protected $locales = [];

    /** @var Generator */
    protected $faker;

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressHelper $progress, array $options = [])
    {
        $this->locales = $options['locales'];

        $associationTypeFile = $globalConfig['output_dir'].'/'.self::ASSOCIATION_TYPES_FILENAME;
        $delimiter = $config['delimiter'];

        $data = [];
        $headers = [];

        $this->faker = \Faker\Factory::create();
        if (isset($globalConfig['seed'])) {
            $this->faker->seed($globalConfig['seed']);
        }

        for ($i = 0; $i < $config['count']; $i++) {
            $associationType = $this->generateAssociationType();
            $associationType = $this->normalizeAssociationType($associationType);
            $data[] = $associationType;

            $headers = array_unique(array_merge($headers, array_keys($associationType)));

            $progress->advance();
        }

        $this->writeCsvFile($data, $headers, $associationTypeFile, $delimiter);

        $progress->advance();

        return $this;
    }

    /**
     * Write the CSV file from attributes
     *
     * @param array  $associationTypes
     * @param array  $headers
     * @param string $outputFile
     * @param string $delimiter
     *
     * @internal param array $attributes
     */
    protected function writeCsvFile(array $associationTypes, array $headers, $outputFile, $delimiter)
    {
        $csvFile = fopen($outputFile, 'w');

        fputcsv($csvFile, $headers, $delimiter);
        $headersAsKeys = array_fill_keys($headers, "");

        foreach ($associationTypes as $attribute) {
            $attributeData = array_merge($headersAsKeys, $attribute);
            fputcsv($csvFile, $attributeData, $delimiter);
        }
        fclose($csvFile);
    }

    /**
     * Generate fake association type
     *
     * @return AssociationType
     */
    protected function generateAssociationType()
    {
        $associationType = new AssociationType();
        $associationType->setCode(strtoupper($this->faker->word()));

        foreach ($this->locales as $locale) {
            $translation = new AssociationTypeTranslation();
            $translation->setLocale($locale->getCode());
            $translation->setLabel($this->faker->word());
            $associationType->addTranslation($translation);
        }

        return $associationType;
    }

    /**
     * @param AssociationTypeInterface $associationType
     *
     * @return array
     */
    protected function normalizeAssociationType(AssociationTypeInterface $associationType)
    {
        $result = ['code' => $associationType->getCode()];

        foreach ($associationType->getTranslations() as $translation) {
            $result[sprintf('label-%s', $translation->getLocale())] = $translation->getLabel();
        }

        return $result;
    }
}
