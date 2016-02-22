<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Faker;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\DataGeneratorBundle\Generator\Product\ProductValueBuilder;
use Pim\Bundle\DataGeneratorBundle\VariantGroupDataProvider;
use Symfony\Component\Console\Helper\ProgressHelper;

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

    const CATEGORY_FIELD = 'categories';
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

    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var string */
    protected $identifierCode;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /** @var Faker\Generator */
    protected $faker;

    /** @var array */
    protected $categoryCodes;

    /** @var VariantGroupDataProvider[] */
    protected $variantGroupDataProviders;

    /** @var string */
    protected $tmpFile;

    /** @var array */
    protected $headers;

    /** @var ProductValueBuilder */
    protected $valueBuilder;

    /**
     * @param ProductValueBuilder          $valueBuilder
     * @param FamilyRepositoryInterface    $familyRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param CategoryRepositoryInterface  $categoryRepository
     * @param GroupRepositoryInterface     $groupRepository
     */
    public function __construct(
        ProductValueBuilder $valueBuilder,
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        CategoryRepositoryInterface $categoryRepository,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->valueBuilder        = $valueBuilder;
        $this->familyRepository    = $familyRepository;
        $this->categoryRepository  = $categoryRepository;
        $this->attributeRepository = $attributeRepository;
        $this->groupRepository     = $groupRepository;

        $this->headers = [];

        $this->attributesByFamily = [];
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressHelper $progress, array $options = [])
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'data-gene');

        if (!empty($config['filename'])) {
            $this->outputFile = $globalConfig['output_dir'].'/'.trim($config['filename']);
        } else {
            $this->outputFile = $globalConfig['output_dir'].'/'.self::DEFAULT_FILENAME;
        }

        $count               = (int) $config['count'];
        $nbAttrBase          = (int) $config['filled_attributes_count'];
        $nbAttrDeviation     = (int) $config['filled_attributes_standard_deviation'];
        $startIndex          = (int) $config['start_index'];
        $categoriesCount     = (int) $config['categories_count'];
        $variantGroupCount   = (int) $config['products_per_variant_group'];
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

        $this->variantGroupDataProviders = [];
        if ($variantGroupCount > 0) {
            foreach ($this->groupRepository->getAllVariantGroups() as $variantGroup) {
                $this->variantGroupDataProviders[] = new VariantGroupDataProvider($variantGroup, $variantGroupCount);
            }
        }

        if (count($this->variantGroupDataProviders) * $variantGroupCount > $count) {
            throw new \Exception(sprintf(
                'You require too much products per variant group (%s). '.
                'There is only %s variant groups for %s required products',
                $variantGroupCount,
                count($this->variantGroupDataProviders),
                $count
            ));
        }

        $this->identifierCode = $this->attributeRepository->getIdentifierCode();

        $this->faker = Faker\Factory::create();
        if (isset($globalConfig['seed'])) {
            $this->faker->seed($globalConfig['seed']);
        }
        $this->valueBuilder->setFakerGenerator($this->faker);

        for ($i = $startIndex; $i < ($startIndex + $count); $i++) {
            $product = [];
            $product[$this->identifierCode] = self::IDENTIFIER_PREFIX . $i;
            $family = $this->getRandomFamily($this->faker);
            $product['family'] = $family->getCode();
            $product['groups'] = '';

            $variantGroupDataProvider = $this->getNextVariantGroupProvider();
            $variantGroupAttributes = [];

            if (null !== $variantGroupDataProvider) {
                $variantGroupAttributes = $variantGroupDataProvider->getAttributes();
                $product['groups'] = $variantGroupDataProvider->getCode();
            }

            $nbAttr = $this->getAttributesCount(
                $nbAttrBase - count($variantGroupAttributes),
                $nbAttrDeviation,
                $family
            );
            $attributes = $this->getRandomAttributesFromFamily($family, $nbAttr);

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

            if (null !== $variantGroupDataProvider) {
                $product = array_merge($product, $variantGroupDataProvider->getData());
            }

            $categories = $this->getRandomCategoryCodes($categoriesCount);

            $product[self::CATEGORY_FIELD] = implode(',', $categories);

            $this->bufferizeProduct($product);

            $progress->advance();
        }

        $this->writeCsvFile();

        unlink($this->tmpFile);

        return $this;
    }

    /**
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    protected function generateValue(AttributeInterface $attribute)
    {
        if (isset($this->forcedValues[$attribute->getCode()])) {
            return $this->forcedValues[$attribute->getCode()];
        }

        return $this->valueBuilder->build($attribute);
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
     * @return AttributeInterface
     */
    protected function getRandomAttribute($faker)
    {
        return $this->getRandomItem($faker, $this->attributeRepository, $this->attributes);
    }

    /**
     * Get non-identifier attribute from family
     *
     * @param FamilyInterface $family
     *
     * @return AttributeInterface[]
     */
    protected function getAttributesFromFamily(FamilyInterface $family)
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
     * @param FamilyInterface $family
     * @param int             $count
     *
     * @return AttributeInterface[]
     */
    protected function getRandomAttributesFromFamily(FamilyInterface $family, $count)
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
     * Write the CSV file from data coming from the buffer
     */
    protected function writeCsvFile()
    {
        $buffer = fopen($this->tmpFile, 'r');

        $csvFile = fopen($this->outputFile, 'w');

        fputcsv($csvFile, $this->headers, $this->delimiter);
        $headersAsKeys = array_fill_keys($this->headers, "");

        while ($bufferedProduct = fgets($buffer)) {
            $product = unserialize($bufferedProduct);
            $productData = array_merge($headersAsKeys, $product);
            fputcsv($csvFile, $productData, $this->delimiter);
        }
        fclose($csvFile);
        fclose($buffer);
    }

    /**
     * Bufferize the product for latter use and set the headers
     *
     * @param array $product
     */
    protected function bufferizeProduct(array $product)
    {
        $this->headers = array_unique(array_merge($this->headers, array_keys($product)));

        file_put_contents($this->tmpFile, serialize($product)."\n", FILE_APPEND);
    }

    /**
     * Returns the number of attributes to set.
     *
     * @param int             $nbAttrBase
     * @param int             $nbAttrDeviation
     * @param FamilyInterface $family
     *
     * @return int
     */
    protected function getAttributesCount($nbAttrBase, $nbAttrDeviation, $family)
    {
        if ($nbAttrBase > 0) {
            if ($nbAttrDeviation > 0) {
                $nbAttr = $this->faker->numberBetween(
                    $nbAttrBase - round($nbAttrDeviation/2),
                    $nbAttrBase + round($nbAttrDeviation/2)
                );
            } else {
                $nbAttr = $nbAttrBase;
            }
        }
        $familyAttrCount = count($this->getAttributesFromFamily($family));

        if (!isset($nbAttr) || $nbAttr > $familyAttrCount) {
            $nbAttr = $familyAttrCount;
        }

        return $nbAttr;
    }

    /**
     * Get a random variantGroupProvider. If this is the last usage of it, removes it from the list.
     * If there is no remaining VariantGroupProvider, returns null.
     *
     * @return VariantGroupDataProvider|null
     */
    protected function getNextVariantGroupProvider()
    {
        $variantGroupProvider = null;

        if (count($this->variantGroupDataProviders) > 0) {
            $variantGroupProviderIndex = $this->faker->numberBetween(0, count($this->variantGroupDataProviders) - 1);
            $variantGroupProvider = $this->variantGroupDataProviders[$variantGroupProviderIndex];

            if ($variantGroupProvider->isLastUsage()) {
                array_splice($this->variantGroupDataProviders, $variantGroupProviderIndex, 1);
            }
        }

        return $variantGroupProvider;
    }
}
