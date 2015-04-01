<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CategoryRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Symfony\Component\Console\Helper\ProgressHelper;
use Faker;

/**
 * Generate native CSV file for products
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductGenerator implements GeneratorInterface
{
    const DEFAULT_FILENAME = 'products.csv';
    const IDENTIFIER_PREFIX = 'id-';
    const METRIC_UNIT = 'unit';

    const CATEGORY_FIELD = 'categories';

    const DEFAULT_NUMBER_MIN = '0';
    const DEFAULT_NUMBER_MAX = '1000';
    const DEFAULT_NB_DECIMALS = '4';
    const DEFAULT_DELIMITER = ',';

    /** @var string */
    protected $outputFile;

    /** @var string */
    protected $delimiter;

    /** @var array */
    protected $forcedValues;

    /** @var array */
    protected $families;

    /** @var array */
    protected $attributes;

    /** @var array */
    protected $attributesByFamily;

    /** @var array */
    protected $currencies;

    /** @var array */
    protected $channels;

    /** @var array */
    protected $locales;

    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var string */
    protected $identifierCode;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var CurrencyRepositoryInterface */
    protected $currencyRepository;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var Faker\Generator */
    protected $faker;

    /** @var array */
    protected $categoryCodes;

    /**
     * @param FamilyRepositoryInterface    $familyRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ChannelRepositoryInterface   $channelRepository
     * @param LocaleRepositoryInterface    $localeRepository
     * @param CurrencyRepositoryInterface  $currencyRepository
     * @param CategoryRepositoryInterface  $categoryRepository
     */
    public function __construct(
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        CurrencyRepositoryInterface $currencyRepository,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->familyRepository   = $familyRepository;
        $this->channelRepository  = $channelRepository;
        $this->localeRepository   = $localeRepository;
        $this->currencyRepository = $currencyRepository;
        $this->categoryRepository = $categoryRepository;
        $this->attributeRepository = $attributeRepository;

        $this->attributesByFamily = [];
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress, array $options = null)
    {
        if (!empty($config['filename'])) {
            $this->outputFile = $outputDir.'/'.trim($config['filename']);
        } else {
            $this->outputFile = $outputDir.'/'.self::DEFAULT_FILENAME;
        }

        $count               = (int) $config['count'];
        $nbValuesBase        = (int) $config['values_count'];
        $nbValueDeviation    = (int) $config['values_count_standard_deviation'];
        $startIndex          = (int) $config['start_index'];
        $categoriesCount     = (int) $config['categories_count'];
        $mandatoryAttributes = $config['mandatory_attributes'];
        if (!is_array($mandatoryAttributes)) {
            $mandatoryAttributes = [];
        }
        $delimiter = $config['delimiter'];

        $this->delimiter = ($delimiter != null) ? $delimiter : self::DEFAULT_DELIMITER;

        if (isset($config['force_values'])) {
            $this->forcedValues = $config['force_values'];
        } else {
            $this->forcedValues = [];
        }

        $this->identifierCode = $this->attributeRepository->getIdentifierCode();

        $this->faker = Faker\Factory::create();

        $products = [];

        for ($i = $startIndex; $i < ($startIndex + $count); $i++) {
            $product = [];
            $product[$this->identifierCode] = self::IDENTIFIER_PREFIX . $i;
            $family = $this->getRandomFamily($this->faker);
            $product['family'] = $family->getCode();

            if ($nbValuesBase > 0) {
                if ($nbValueDeviation > 0) {
                    $nbValues = $this->faker->numberBetween(
                        $nbValuesBase - round($nbValueDeviation/2),
                        $nbValuesBase + round($nbValueDeviation/2)
                    );
                } else {
                    $nbValues = $nbValuesBase;
                }
            }
            $familyAttrCount = count($this->getAttributesFromFamily($family));

            if (!isset($nbValues) || $nbValues > $familyAttrCount) {
                $nbValues = $familyAttrCount;
            }

            $attributes = $this->getRandomAttributesFromFamily($family, $nbValues);
            foreach ($attributes as $attribute) {
                $valueData = $this->generateValue($attribute);
                $product = array_merge($product, $valueData);
            }

            foreach ($mandatoryAttributes as $mandatoryAttribute) {
                if (isset($this->attributesByFamily[$family->getCode()][$mandatoryAttribute])) {
                    $attribute = $this->attributesByFamily[$family->getCode()][$mandatoryAttribute];
                    $valueData = $this->generateValue($attribute);
                    $product = array_merge($product, $valueData);
                }
            }

            $categories = $this->getRandomCategoryCodes($categoriesCount);

            $product[self::CATEGORY_FIELD] = implode(',', $categories);

            $products[] = $product;
            $progress->advance();
        }

        $headers = $this->getAllKeys($products);

        $this->writeCsvFile($products, $headers);

        return $this;
    }

    /**
     * Generate a value in term of one or several entries in the product array
     *
     * @param AbstractAttribute $attribute
     *
     * @return array
     */
    protected function generateValue(AbstractAttribute $attribute)
    {
        $valueData = [];
        $keys = $this->getAttributeKeys($attribute);

        foreach ($keys as $key) {
            $valueData[$key] = $this->generateValueData($attribute, $key);
        }

        return $valueData;
    }

    /**
     * Provides the potential column keys for this attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return array
     */
    protected function getAttributeKeys(AbstractAttribute $attribute)
    {
        $keys = [];

        $keys[] = $attribute->getCode();

        $updatedKeys = [];
        if ($attribute->isScopable() && $attribute->isLocalizable()) {
            foreach ($this->getLocales() as $locale) {
                foreach ($this->getChannels() as $channel) {
                    foreach ($keys as $baseKey) {
                        $key = $baseKey.'-'.$locale->getCode().'-'.$channel->getCode();
                        $updatedKeys[] = $key;
                    }
                }
            }
            $keys = $updatedKeys;

        } elseif ($attribute->isScopable() && !$attribute->isLocalizable()) {
            foreach ($this->getChannels() as $channel) {
                foreach ($keys as $baseKey) {
                    $key = $baseKey.'-'.$channel->getCode();
                    $updatedKeys[] = $key;
                }
            }

            $keys = $updatedKeys;
        } elseif (!$attribute->isScopable() && $attribute->isLocalizable()) {
            foreach ($this->getLocales() as $locale) {
                foreach ($keys as $baseKey) {
                    $key = $baseKey.'-'.$locale->getCode();
                    $updatedKeys[] = $key;
                }
            }
            $keys = $updatedKeys;
        }

        switch ($attribute->getBackendType()) {
            case 'prices':
                $updatedKeys = [];

                foreach ($keys as $key) {
                    foreach ($this->getCurrencies() as $currency) {
                        $updatedKeys[] = $key.'-'.$currency->getCode();
                    }
                }
                $keys = $updatedKeys;
                break;
            case 'metric':
                $updatedKeys = [];

                foreach ($keys as $key) {
                    $updatedKeys[] = $key;
                    $updatedKeys[] = $key.'-'.self::METRIC_UNIT;

                }
                $keys = $updatedKeys;
                break;
        }

        return $keys;
    }

    /**
     * Generate value content based on backend type
     *
     * @param AbstractAttribute $attribute
     * @param string            $key
     *
     * @return string
     */
    protected function generateValueData(AbstractAttribute $attribute, $key)
    {
        $data = "";

        if (isset($this->forcedValues[$attribute->getCode()])) {
            return $this->forcedValues[$attribute->getCode()];
        }

        switch ($attribute->getBackendType()) {
            case "varchar":
                $validationRule = $attribute->getValidationRule();
                switch ($validationRule) {
                    case 'url':
                        $data = $this->faker->url();
                        break;
                    default:
                        $data = $this->faker->sentence();
                        break;
                }
                break;
            case "text":
                $data = $this->faker->sentence();
                break;
            case "date":
                $data = $this->faker->dateTimeBetween($attribute->getDateMin(), $attribute->getDateMax());
                $data = $data->format('Y-m-d');
                break;
            case "metric":
            case "decimal":
            case "prices":
                if ($attribute->getBackendType() && preg_match('/-'.self::METRIC_UNIT.'$/', $key)) {
                    $data = $attribute->getDefaultMetricUnit();
                } else {
                    $min = ($attribute->getNumberMin() != null) ? $attribute->getNumberMin() : self::DEFAULT_NUMBER_MIN;
                    $max = ($attribute->getNumberMax() != null) ? $attribute->getNumberMax() : self::DEFAULT_NUMBER_MAX;

                    $decimals = $attribute->isDecimalsAllowed() ? self::DEFAULT_NB_DECIMALS : 0;

                    $data = $this->faker->randomFloat($decimals, $min, $max);
                }
                break;
            case "boolean":
                $data = $this->faker->boolean() ? "1" : "0";
                break;
            case "option":
            case "options":
                $options = [];
                foreach ($attribute->getOptions() as $option) {
                    $options[] = $option;
                }
                $option = $this->faker->randomElement($options);

                if (is_object($option)) {
                    $data = $option->getCode();
                }

                break;
            default:
                $data = '';
                break;
        }

        return (string) $data;
    }

    /**
     * Get a random family
     *
     * @param mixed $faker
     *
     * @return Family
     */
    protected function getRandomFamily($faker)
    {
        return $this->getRandomItem($faker, $this->familyRepository, $this->families);
    }

    /**
     * Get a random attribute
     *
     * @param mixed $faker
     *
     * @return Attribute
     */
    protected function getRandomAttribute($faker)
    {
        return $this->getRandomItem($faker, $this->attributeRepository, $this->attributes);
    }

    /**
     * Get non-identifier attribute from family
     *
     * @param Family $family
     *
     * @return Attribute[]
     */
    protected function getAttributesFromFamily(Family $family)
    {
        $familyCode = $family->getCode();

        if (!isset($this->attributesByFamily[$familyCode])) {
            $this->attributesByFamily[$familyCode] = [];

            $attributes = $family->getAttributes();
            foreach ($attributes as $attribute) {
                if ($attribute->getCode() !== $this->identifierCode) {
                    $this->attributesByFamily[$familyCode][$attribute->getCode()] = $attribute;
                }
            }
        }

        return $this->attributesByFamily[$familyCode];
    }

    /**
     * Get random attributes from the family
     *
     * @param Family $family
     * @param int    $count
     *
     * @return Attribute[]
     */
    protected function getRandomAttributesFromFamily(Family $family, $count)
    {
        return $this->faker->randomElements($this->getAttributesFromFamily($family), $count);
    }

    /**
     * Get random categories
     *
     * @param int $count
     *
     * @return array
     */
    protected function getRandomCategoryCodes($count)
    {
        return $this->faker->randomElements($this->getCategoryCodes(), $count);
    }

    /**
     * Get all categories that are not root
     *
     * @return string[]
     */
    protected function getCategoryCodes()
    {
        if (null === $this->categoryCodes) {
            $this->categoryCodes = [];
            $categories = $this->categoryRepository->findAll();
            foreach ($categories as $category) {
                if (null !== $category->getParent()) {
                    $this->categoryCodes[] = $category->getCode();
                }
            }
        }

        return $this->categoryCodes;
    }

    /**
     * Get all channels
     *
     * @return array
     */
    protected function getChannels()
    {
        if (null === $this->channels) {
            $this->channels = [];
            $channels = $this->channelRepository->findAll();
            foreach ($channels as $channel) {
                $this->channels[$channel->getCode()] = $channel;
            }
        }

        return $this->channels;
    }

    /**
     * Get active currencies
     *
     * @return array
     */
    protected function getCurrencies()
    {
        if (null === $this->currencies) {
            $this->currencies = [];
            $currencies = $this->currencyRepository->findBy(['activated' => 1]);
            foreach ($currencies as $currency) {
                $this->currencies[$currency->getCode()] = $currency;
            }
        }

        return $this->currencies;
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
     * Get a random item from a repo
     *
     * @param Faker\Generator  $faker
     * @param ObjectRepository $repo
     * @param array            &$items
     *
     * @return mixed
     */
    protected function getRandomItem(Faker\Generator $faker, ObjectRepository $repo, array &$items = null)
    {
        if (null === $items) {
            $items = [];
            $loadedItems = $repo->findAll();
            foreach ($loadedItems as $item) {
                $items[$item->getCode()] = $item;
            }
        }

        return $faker->randomElement($items);
    }

    /**
     * Get a set of all keys inside arrays
     *
     * @param array $products
     *
     * @return array
     */
    protected function getAllKeys(array $products)
    {
        $keys = [];

        foreach ($products as $product) {
            $keys = array_merge($keys, array_keys($product));
            $keys = array_unique($keys);
        }

        return $keys;
    }

    /**
     * Write the CSV file from products and headers
     *
     * @param array $products
     * @param array $headers
     */
    protected function writeCsvFile(array $products, array $headers)
    {
        $csvFile = fopen($this->outputFile, 'w');

        fputcsv($csvFile, $headers, $this->delimiter);
        $headersAsKeys = array_fill_keys($headers, "");

        foreach ($products as $product) {
            $productData = array_merge($headersAsKeys, $product);
            fputcsv($csvFile, $productData, $this->delimiter);
        }
        fclose($csvFile);
    }

    public function setExtraAttributes(array $extraAttributes)
    {
        // TODO not implemented
    }
}
