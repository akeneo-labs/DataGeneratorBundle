<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Repository\FamilyRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\LocaleRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\ChannelRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\CurrencyRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Faker;

/**
 * Generate native CSV file for products
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCsvGenerator implements GeneratorInterface
{
    const OUTFILE='product.csv';
    const SKU_PREFIX='sku-';

    /**
     * @var array
     */
    protected $families;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var array
     */
    protected $attributesByFamily;

    /**
     * @var array
     */
    protected $currencies;

    /**
     * @var array
     */
    protected $channels;

    /**
     * @var array
     */
    protected $locales;

    /**
     * @var FamilyRepository
     */
    protected $familyRepository;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var CurrencyRepository
     */
    protected $currencyRepository;

    /**
     * @param FamilyRepository
     * @param AttributeRepository
     * @param ChannelRepository
     * @param LocaleRepository
     * @param CurrencyRepository
     */
    public function __construct(
        FamilyRepository $familyRepository,
        AttributeRepository $attributeRepository,
        ChannelRepository $channelRepository,
        LocaleRepository $localeRepository,
        CurrencyRepository $currencyRepository
    ) {
        $this->familyRepository = $familyRepository;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->currencyRepository = $currencyRepository;

        $this->attributeByFamily = array();
    }

    /**
     * {@inheritdoc}
     */
    public function generate($amount, array $options)
    {
        $nbValuesBase = (int) $options['values-number'];
        $nbValueDeviation = (int) $options['values-number-standard-deviation'];

        $commonFaker = Faker\Factory::create();

        $products = [];

        for ($i = 0; $i < $amount; $i++) {
            $product = array();
            $product['sku'] = self::SKU_PREFIX . $i;
            $family = $this->getRandomFamily($commonFaker);
            $product['family'] = $family->getCode();

            if ($nbValuesBase > 0) {
                if ($nbValueDeviation > 0) {
                    $nbValues = $commonFaker->randomNumber(
                        $nbValuesBase - round($nbValueDeviation/2),
                        $nbValuesBase + round($nbValueDeviation/2)
                    );
                } else {
                    $nbValues = $nbValuesBase;
                }
            }
                    
            if (!isset($nbValues) || $nbValues > $family->getAttributes()->count()) {
                $nbValues = $family->getAttributes()->count();
            }

            $attributeFaker = Faker\Factory::create();
            $attributeFaker = $attributeFaker->unique();

            for ($j = 0; $j < $nbValues; $j++) {
                $attribute = $this->getRandomAttributeFromFamily($attributeFaker, $family);
                $valueData = $this->generateValue($attribute);
                $product = array_merge($product, $valueData);
            }

            $products[] = $product;
        }

        $headers = $this->getAllKeys($products);

        print_r($headers);

        return $this;
    }

    /**
     * Generate a value in term of one or several entries in the product array
     *
     * @param AbstractAttribute $attribute
     *
     * @return array
     */
    public function generateValue(AbstractAttribute $attribute)
    {
        $valueData = array();
        $keys = $this->getAttributeKeys($attribute);

        foreach ($keys as $key) {
            $valueData[$key] = $this->generateValueData($attribute);
        }

        return $valueData;
    }

    /**
     * Provides the potential column keys for this attribute
     *
     * @param AbstractAttribute $attribtue
     *
     * @return array
     */
    public function getAttributeKeys(AbstractAttribute $attribute)
    {
        $keys = array();

        if ('prices' === $attribute->getBackendType()) {
            foreach ($this->getCurrencies() as $currency) {
                $keys[] = $attribute->getCode().'-'.$currency->getCode();
            }
        } else {
            $keys[] = $attribute->getCode();
        }

        $updatedKeys = array();
        if ($attribute->isScopable() && $attribute->isLocalizable()) {
            foreach ($this->getLocales() as $locale) {
                foreach ($this->getChannels() as $channel) {

                    foreach ($keys as $baseKey) {
                        $key = $baseKey.'-'.$locale->getCode().'-'.$channel->getCode();
                        $updatedKeys[] = $baseKey;
                    }
                }
            }
            $keys = $updatedKeys;

        } elseif ($attribute->isScopable() && !$attribute->isLocalizable()) {
            foreach ($this->getChannels() as $channel) {
                foreach ($keys as $baseKey) {
                    $key = $baseKey.'-'.$channel->getCode();
                    $updatedKeys[] = $baseKey;
                }
            }

            $keys = $updatedKeys;
        } elseif (!$attribute->isScopable() && $attribute->isLocalizable()) {
            foreach ($this->getLocales() as $locale) {
                foreach ($keys as $baseKey) {
                    $key = $baseKey.'-'.$locale->getCode();
                    $updatedKeys[] = $baseKey;
                }
            }
            $keys = $updatedKeys;
        }

        return $keys;
    }

    /**
     * Generate value content based on backend type
     *
     * @param AbstractAttribute $attribute
     *
     * @return string
     */
    public function generateValueData(AbstractAttribute $attribute)
    {
        $data = "";
        $faker = Faker\Factory::create();

        switch ($attribute->getBackendType()) {
            case "varchar":
                $data = $faker->sentence();
                break;
            case "text":
                $data = $faker->text();
                break;
            case "date":
                $data = $faker->date();
                break;
            case "metric":
                $data = (string) $faker->randomNumber(1, 100);
                break;
            case "prices":
            case "decimal":
                $data = (string) $faker->randomFloat();
                break;
            case "boolean":
                $data = $faker->boolean() ? "1" : "0";
                break;
            case "option":
            case "options":
                $options = array();
                foreach ($attribute->getOptions() as $option) {
                    $options[] = $option;
                }
                $option = $faker->randomElement($options);

                $data = $option->getCode();


                break;
            default:
                $data = '['.$attribute->getBackendType().']';
                break;

        }

        return $data;
    }
    

    /**
     * Get a random family 
     *
     * @return Family
     */
    public function getRandomFamily($faker)
    {
        return $this->getRandomItem($faker, $this->familyRepository, $this->families);
    }

    /**
     * Get a random attribute
     *
     * @param $faker
     *
     * @return Family
     */
    public function getRandomAttribute($faker)
    {
        return $this->getRandomItem($faker, $this->attributeRepository, $this->attributes);
    }

    /**
     * Get a random attribute from the family
     *
     * @param $faker
     * @param Family $family
     *
     * @return $attribute
     */
    public function getRandomAttributeFromFamily($faker, Family $family)
    {
        $familyCode = $family->getCode();

        if (!isset($this->attributesByFamily[$familyCode])) {
            $this->attributesByFamily[$familyCode] = [];

            $attributes = $family->getAttributes();
            foreach ($attributes as $attribute) {
                $this->attributesByFamily[$familyCode][$attribute->getCode()] = $attribute;
            }
        }

        return $faker->randomElement($this->attributesByFamily[$familyCode]);
    }



    /**
     * Get all channels
     *
     * @return array
     */
    public function getChannels()
    {
        if (null === $this->channels) {
            $this->channels = array();
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
    public function getCurrencies()
    {
        if (null === $this->currencies) {
            $this->currencies = array();
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
    public function getLocales()
    {
        if (null === $this->locales) {
            $this->locales = array();
            $locales = $this->localeRepository->findBy(['activated' => 1]);
            foreach ($locales as $currency) {
                $this->currencies[$currency->getCode()] = $currency;
            }
        }
        
        return $this->locales;
    }

    /**
     * Get a random item from a repo
     *
     * @parma $faker
     * @param EntityRepository $repo
     * @param array
     *
     * @return element
     */
    public function getRandomItem($faker, ObjectRepository $repo, array &$items = null)
    {
        if (null === $items) {
            $items = array();
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
     */
    public function getAllKeys(array $products)
    {
        $keys = [];
        
        foreach ($products as $product) {
            $keys = array_unique($keys + array_keys($product));
        }

        return $keys;
    }

}
