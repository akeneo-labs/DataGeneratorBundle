<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker\Factory;
use Faker\Generator;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\AssociationTypeTranslation;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Yaml;

/**
 * Generate native CSV file for association types.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeGenerator implements GeneratorInterface
{
    const TYPE = 'association_types';

    const ASSOCIATION_TYPES_FILENAME = 'association_types.csv';

    const ASSOCIATIONS = 'associations';

    /** @var CsvWriter */
    protected $writer;

    /** @var Locale[] */
    protected $locales = [];

    /** @var Generator */
    protected $faker;

    /**
     * @param CsvWriter $writer
     */
    public function __construct(CsvWriter $writer)
    {
        $this->writer = $writer;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $entitiesConfig, ProgressBar $progress, array $options = [])
    {
        $this->locales = $options['locales'];

        $data = [];
        $this->faker = Factory::create();
        if (isset($globalConfig['seed'])) {
            $this->faker->seed($globalConfig['seed']);
        }

        for ($i = 0; $i < $entitiesConfig['count']; $i++) {
            $associationType = $this->generateAssociationType();
            $data[] = $this->normalizeAssociationType($associationType);

            $progress->advance();
        }

        $this->writer
            ->setFilename(sprintf(
                '%s%s%s',
                $globalConfig['output_dir'],
                DIRECTORY_SEPARATOR,
                self::ASSOCIATION_TYPES_FILENAME
            ))
            ->write($data);

        $progress->advance();

        return [];
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

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return self::TYPE === $type;
    }
}
