<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker\Factory;
use Faker\Generator;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
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

    /** @var CsvWriter */
    protected $writer;

    /** @var array */
    protected $locales = [];

    /** @var array */
    protected $attributeOptions = [];

    /** @var array */
    protected $attributes = [];

    /** @var array */
    protected $selectAttributes;

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
    public function generate(array $globalConfig, array $config, ProgressHelper $progress, array $options = [])
    {
        $this->locales    = $options['locales'];
        $this->attributes = $options['attributes'];

        $this->faker = Factory::create();
        if (isset($globalConfig['seed'])) {
            $this->faker->seed($globalConfig['seed']);
        }

        $countPerAttribute = (int) $config['count_per_attribute'];

        foreach ($this->getSelectAttributes() as $attribute) {
            for ($i = 0; $i < $countPerAttribute; $i++) {
                $attributeOption = [];
                $attributeOption['attribute'] = $attribute->getCode();
                $attributeOption['code'] = self::ATTRIBUTE_OPTION_CODE_PREFIX . $attribute->getCode() . $i;
                $attributeOption['sort_order'] = $this->faker->numberBetween(1, $countPerAttribute);

                foreach ($this->getLocalizedRandomLabels() as $localeCode => $label) {
                    $attributeOption['label-'.$localeCode] = $label;
                }

                $this->attributeOptions[$attributeOption['code']] = $attributeOption;
            };
        }

        $this->writer
            ->setFilename(sprintf(
                '%s%s%s',
                $globalConfig['output_dir'],
                DIRECTORY_SEPARATOR,
                self::ATTRIBUTE_OPTIONS_FILENAME
            ))
            ->write($this->attributeOptions);

        $progress->advance();

        return $this;
    }

    /**
     * Get localized random labels
     *
     * @return LocaleInterface[]
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
     * Get attributes that can have options
     *
     * @return AttributeInterface[]
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
                if (in_array($attribute->getAttributeType(), [
                    AttributeTypes::OPTION_SIMPLE_SELECT,
                    AttributeTypes::OPTION_MULTI_SELECT
                ])) {
                    $this->selectAttributes[$attribute->getCode()] = $attribute;
                }
            }
        }

        return $this->selectAttributes;
    }
}
