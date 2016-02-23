<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Faker;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\DataGeneratorBundle\Generator\Product\ProductRawBuilder;
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
    private $outputFile;

    /** @var string */
    private $delimiter;

    /** @var array */
    private $families;

    /** @var array */
    private $attributesByFamily;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var Faker\Generator */
    private $faker;

    /** @var array */
    private $categoryCodes;

    /** @var VariantGroupDataProvider[] */
    private $variantGroupDataProviders;

    /** @var string */
    private $tmpFile;

    /** @var array */
    private $headers;

    /** @var ProductValueBuilder */
    private $valueBuilder;

    /** @var ProductRawBuilder */
    private $productRawBuilder;

    /**
     * @param ProductValueBuilder          $valueBuilder
     * @param ProductRawBuilder            $productRawBuilder
     * @param FamilyRepositoryInterface    $familyRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param CategoryRepositoryInterface  $categoryRepository
     * @param GroupRepositoryInterface     $groupRepository
     */
    public function __construct(
        ProductValueBuilder $valueBuilder,
        ProductRawBuilder $productRawBuilder,
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        CategoryRepositoryInterface $categoryRepository,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->valueBuilder        = $valueBuilder;
        $this->productRawBuilder   = $productRawBuilder;
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
        $forcedValues = $config['force_values'];

        if (!is_array($mandatoryAttributes)) {
            $mandatoryAttributes = [];
        }
        $delimiter = $config['delimiter'];

        $this->delimiter = ($delimiter != null) ? $delimiter : self::DEFAULT_DELIMITER;

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

        $identifierCode = $this->attributeRepository->getIdentifierCode();

        $this->faker = Faker\Factory::create();
        if (isset($globalConfig['seed'])) {
            $this->faker->seed($globalConfig['seed']);
        }
        $this->valueBuilder->setFakerGenerator($this->faker);
        $this->productRawBuilder->setFakerGenerator($this->faker);

        for ($i = $startIndex; $i < ($startIndex + $count); $i++) {
            $product = [];
            $product[$identifierCode] = self::IDENTIFIER_PREFIX . $i;
            $family = $this->getRandomFamily($this->faker);
            $product['family'] = $family->getCode();
            $product['groups'] = '';

            $variantGroupDataProvider = $this->getNextVariantGroupProvider();
            $variantGroupAttributes = [];

            if (null !== $variantGroupDataProvider) {
                $variantGroupAttributes = $variantGroupDataProvider->getAttributes();
                $product['groups'] = $variantGroupDataProvider->getCode();
            }

            $this->productRawBuilder->fillInRandomProperties(
                $family,
                $product,
                $forcedValues,
                $nbAttrBase - count($variantGroupAttributes),
                $nbAttrDeviation
            );
            $this->productRawBuilder->fillInMandatoryProperties($family, $product, $forcedValues, $mandatoryAttributes);

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
     * Get a random family
     *
     * @param mixed $faker
     *
     * @return Family
     */
    private function getRandomFamily($faker)
    {
        return $this->getRandomItem($faker, $this->familyRepository, $this->families);
    }

    /**
     * Get random categories
     *
     * @param int $count
     *
     * @return array
     */
    private function getRandomCategoryCodes($count)
    {
        return $this->faker->randomElements($this->getCategoryCodes(), $count);
    }

    /**
     * Get all categories that are not root
     *
     * @return string[]
     */
    private function getCategoryCodes()
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
    private function getRandomItem(Faker\Generator $faker, ObjectRepository $repo, array &$items = null)
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
    private function writeCsvFile()
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
    private function bufferizeProduct(array $product)
    {
        $this->headers = array_unique(array_merge($this->headers, array_keys($product)));

        file_put_contents($this->tmpFile, serialize($product)."\n", FILE_APPEND);
    }


    /**
     * Get a random variantGroupProvider. If this is the last usage of it, removes it from the list.
     * If there is no remaining VariantGroupProvider, returns null.
     *
     * @return VariantGroupDataProvider|null
     */
    private function getNextVariantGroupProvider()
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
