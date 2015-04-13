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

    const DEFAULT_FILENAME = 'attribute_options.yml';

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

    /** @var Faker\Generator */
    protected $faker;

    /**
     * @param LocaleRepositoryInterface    $localeRepository
     */
    public function __construct(
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->localeRepository   = $localeRepository;
        $this->faker = Faker\Factory::create();
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress, array $options = null)
    {
        $count = (int) $config['count'];

        $this->outputFile = (!empty($config['filename'])) ?
            $outputDir.'/'.trim($config['filename'])
            :
            $outputDir.'/'.self::DEFAULT_FILENAME;

        foreach ($this->getFilteredAttributes() as $attribute) {
            for ($i = 0; $i < $count; $i++) {
                $attributeOptions = [];
                $attributeOptions['attribute'] = $attribute->getCode();
                $attributeOptions['labels'][] = $this->getLocalizedRandomLabels();
                $attributeOptions['sortOrder'] = $this->faker->numberBetween(1, 10);
                $attributeLabel = self::ATTRIBUTE_OPTION_CODE_PREFIX . $attribute->getCode() . $i;
                $this->attributeOptions[$attributeLabel] = $attributeOptions;
            };
            $progress->advance();
        }
        
        $this->writeYamlFile(['options' => $this->attributeOptions], $this->outputFile);

        return $this;
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
     * Get a random attribute group code
     *
     * @return string
     */
    protected function getRandomAttributeOptionCode()
    {
        return $this->faker->randomElement($this->getAttributesSelectOptions());
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
     * Get filtered attributes
     *
     * @return array
     */
    public function getFilteredAttributes()
    {
        $filteredAttributes = [];

        if (null !== $this->attributes) {
            foreach ($this->attributes as $attribute) {
                if ($attribute->getAttributeType() === 'pim_catalog_simpleselect' ||
                    $attribute->getAttributeType() === 'pim_catalog_multiselect'
                 ) {
                    $filteredAttributes[$attribute->getCode()] = $attribute;
                }

            }
        }

        return $filteredAttributes;
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
