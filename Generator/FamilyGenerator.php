<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
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
    const FAMILIES_FILENAME = 'families.yml';

    const FAMILY_CODE_PREFIX = 'fam_';

    /** @var string */
    protected $familiesFile;

    /** @var string */
    protected $identifierAttribute;

    /** @var string */
    protected $labelAttribute;

    /** @var array */
    protected $locales;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var array */
    protected $channels;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var Faker\Generator */
    protected $faker;

    /** @var array */
    protected $families;

    /** @var array */
    protected $attributes;

    /** @var array */
    protected $filteredAttrCodes;

    /**
     * @param ChannelRepositoryInterface $channelRepository
     * @param LocaleRepositoryInterface  $localeRepository
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress)
    {
        $this->familiesFile = $outputDir.'/'.self::FAMILIES_FILENAME;

        $count = (int) $config['count'];
        $attributesCount = (int) $config['attributes_count'] - 1;
        $requirementsCount = (int) $config['requirements_count'] - 1;
        $this->identifierAttribute = $config['identifier_attribute'];
        $this->labelAttribute      = $config['label_attribute'];

        $this->faker = Faker\Factory::create();

        $families = [];

        for ($i = 0; $i < $count; $i++) {
            $family = [];
            $family['labels'] = $this->getLocalizedRandomLabels();

            $family['attributeAsLabel'] = $this->labelAttribute;

            $attributes = $this->faker->randomElements($this->getAttributeCodes(), $attributesCount);

            $attributes = array_merge([$this->identifierAttribute, $this->labelAttribute], $attributes);
            $family['attributes'] = $attributes;

            $requirements = [];

            foreach ($this->getChannels() as $channel) {
                $attributeReqs = $this->faker->randomElements($this->getAttributeCodes(), $requirementsCount);
                $attributeReqs = array_merge([$this->identifierAttribute], $attributeReqs);

                $requirements[$channel->getCode()] = $attributeReqs;
            }
            $family['requirements'] = $requirements;

            $families[self::FAMILY_CODE_PREFIX.$i] = $family;
            $progress->advance();
        }


        $this->families = [
            "families" => $families
        ];

        $this->writeYamlFile($this->families, $this->familiesFile);

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
     * Set attributes
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
     * Get active locales
     *
     * @return array
     */
    protected function getLocales()
    {
        if (null === $this->locales) {
            $this->locales = array();
            $locales = $this->localeRepository->findBy(['activated' => 1]);
            foreach ($locales as $locale) {
                $this->locales[$locale->getCode()] = $locale;
            }
        }

        return $this->locales;
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
     * Get channels
     *
     * @return array
     */
    protected function getChannels()
    {
        if (null === $this->channels) {
            $this->channels = $this->channelRepository->findAll();
        }

        return $this->channels;
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
