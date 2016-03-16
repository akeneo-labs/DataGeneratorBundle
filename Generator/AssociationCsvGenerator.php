<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Doctrine\Common\Persistence\ObjectRepository;
use Faker;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Component\Catalog\Model\AbstractAttribute;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\Console\Helper\ProgressHelper;

/**
 * Generate native CSV file for association
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationCsvGenerator implements GeneratorInterface
{
    const DEFAULT_DELIMITER = ',';

    const DEFAULT_FILENAME = 'associations.csv';

    /** @var string */
    protected $outputFile;

    /** @var string */
    protected $delimiter;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var string */
    protected $identifierCode;

    /** @var Faker\Generator */
    protected $faker;

    /**
     * @param ProductRepositoryInterface   $productRepository
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->attributeRepository = $attributeRepository;

        $this->identifierCode = $attributeRepository->getIdentifierCode();
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressHelper $progress, array $options = [])
    {
        if (!empty($config['filename'])) {
            $this->outputFile = $globalConfig['output_dir'].'/'.trim($config['filename']);
        } else {
            $this->outputFile = $globalConfig['output_dir'].'/'.self::DEFAULT_FILENAME;
        }

        $this->delimiter = ($options['delimiter'] != null) ? $options['delimiter'] : self::DEFAULT_DELIMITER;

        $this->forcedValues = [];

        $this->faker = \Faker\Factory::create();
        if (isset($globalConfig['seed'])) {
            $this->faker->seed($globalConfig['seed']);
        }

        $associations = [];

        for ($i = 0; $i < $config; $i++) {
            $association = [];
            $association['code'] = $this->getRandomProduct($this->faker);
            $association['products'] = $this->getRandomProducts();
            $association['association_type'] = $this->getRandomAssociationType();

            $associations[] = $association;
            $progress->advance();
        }

        $headers = $this->getAllKeys($products);

        $this->writeCsvFile($products, $headers);

        $progress->advance();

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
     * @return Family
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
     * @return array
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
     * Get a random attribute from the family
     *
     * @param Family $family
     *,@param int    $count
     *
     * @return array
     */
    protected function getRandomAttributesFromFamily(Family $family, $count)
    {
        return $this->faker->randomElements($this->getAttributesFromFamily($family), $count);
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
     * @param mixed            $faker
     * @param ObjectRepository $repo
     * @param array            &$items
     *
     * @return string
     */
    protected function getRandomItem($faker, ObjectRepository $repo, array &$items = null)
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
}
