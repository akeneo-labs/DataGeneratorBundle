<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\AssociationTypeTranslation;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;

/**
 * Generate native YAML file for association types.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeGenerator implements GeneratorInterface
{
    const ASSOCIATION_TYPES_FILENAME = 'association_types.yml';

    const ASSOCIATIONS = 'associations';

    /** @var Locale[] */
    protected $locales;

    /** @var Faker\Generator */
    protected $faker;

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressHelper $progress, array $options = [])
    {
        $this->locales = $options['locales'];

        $data = [];
        $this->faker = \Faker\Factory::create();
        if (isset($globalConfig['seed'])) {
            $this->faker->seed($globalConfig['seed']);
        }

        for ($i = 0; $i < $config['count']; $i++) {
            $associationType = $this->generateAssociationType();
            $data = array_merge($data, $this->normalizeAssociationType($associationType));

            $progress->advance();
        }

        $data = [self::ASSOCIATIONS => $data];

        $this->writeYamlFile($data, $globalConfig['output_dir']);

        $progress->advance();

        return $this;
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
        $code = $associationType->getCode();
        $result = [
            $code => [
                'labels' => []
            ]
        ];

        /** @var AssociationTypeTranslation $translation */
        foreach ($associationType->getTranslations() as $translation) {
            $result[$code]['labels'][$translation->getLocale()] = $translation->getLabel();
        }

        return $result;
    }

    /**
     * Write a YAML file
     *
     * @param array  $data
     * @param string $outputDir
     */
    protected function writeYamlFile(array $data, $outputDir)
    {
        $dumper = new Yaml\Dumper();
        $yamlData = $dumper->dump($data, 4, 0, true, true);

        file_put_contents($outputDir.'/'.self::ASSOCIATION_TYPES_FILENAME, $yamlData);
    }
}
