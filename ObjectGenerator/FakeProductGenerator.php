<?php

namespace Pim\Bundle\DataGeneratorBundle\ObjectGenerator;

use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Bundle\DataGeneratorBundle\Provider\FakeProductValueProvider;
use Pim\Bundle\DataGeneratorBundle\Provider\RandomFamilyProvider;
use Pim\Bundle\DataGeneratorBundle\Provider\RandomCategoryProvider;
use Pim\Bundle\DataGeneratorBundle\Provider\RandomVariantGroupProvider;
use Pim\Bundle\DataGeneratorBundle\Provider\RandomGroupProvider;

/**
 * Generate fake products objects
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FakeProductGenerator
{
    /** @var FakeProductValueProvider */
    protected $valueProvider;

    /** @var RandomFamilyProvider */
    protected $familyProvider;

    /** @var RandomCategoryProvider */
    protected $categoryProvider;

    /** @var RandomVariantGroupProvider */
    protected $variantGroupProvider;

    /** @var RandomGroupProvider */
    protected $groupProvider;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /**
     * @param FakeProductValueProvider   $valueProvider
     * @param RandomFamilyProvider       $familyProvider
     * @param RandomCategoryProvider     $categoryProvider
     * @param RandomVariantGroupProvider $variantGroupProvider
     * @param RandomGroupProvider        $groupProvider
     * @param ProductBuilderInterface    $productBuilder
     */
    public function __construct(
        FakeProductValueProvider $valueProvider,
        RandomFamilyProvider $familyProvider,
        RandomCategoryProvider $categoryProvider,
        RandomVariantGroupProvider $variantGroupProvider,
        RandomGroupProvider $groupProvider,
        ProductBuilder $productBuilder
    ){
        $this->valueProvider        = $valueProvider;
        $this->familyProvider       = $familyProvider;
        $this->categoryProvide      = $categoryProvider;
        $this->variantGroupProvider = $variantGroupProvider;
        $this->groupProvider        = $groupProvider;
        $this->associationProvider  = $associationProvider;
        $this->productBuilder       = $productBuilder;
    }
    /**
     * Generate a product with an the provided identifier and fake product values
     * based on the provided configuration
     *
     * @param string              $identifier
     * @param array               $options
     *
     * @return ProductValueInterface
     */
    public function generateProduct(
        $identifier,
        array $options
    ) {
        $valuesConfig       = isset($options['values_config']) ? $options['values_config'] : [];
        $categoriesCount    = isset($options['categories_count']) ? $options['categories_count'] : 0;
        $variantGroupsCount = isset($options['variant_groups_count']) ? $options['variant_groups_count'] : 0;
        $groupsCount        = isset($options['groups_count']) ? $options['groups_count'] : 0;
        $associationsCount  = isset($options['associations_count']) ? $options['associations_count'] : 0;
        $completeOptions    = isset($options['complete']) ? $options['complete'] : [];

        $family = $this->familyProvider->provideOne();

        $product = $this->productBuilder->createProduct($identifier, $family->getCode());

        $this->addFakeValues($product, $valuesConfig, $completeOptions);
        $this->addCategories($product, $categoriescount);
        $this->addVariantGroups($product, $variantGroupsCount);
        $this->addGroups($product, $groupsCount);

        return $product;
    }

    /**
     * Get product values and add them to the product
     *
     * @param ProductInterface $product
     * @param int              $count
     * @param array            $completeOptions
     */
    protected function addFakeValues(ProductInterface $product, $valuesConfig, array $completeOptions)
    {
        $values = $this->valueProvider->provideSeveral($product->getFamily(), $valuesConfig, $completeOptions);

        foreach($values as $value) {
            $product->addValue($value);
        }
    }

    /**
     * Get categories and add them to the product
     *
     * @param ProductInterface $product
     * @param int              $count
     */
    protected function addCategories($product, $count)
    {
        $categories = $this->categoryProvider->provideSeveral($categoriesCount);

        foreach ($categories as $category) {
            $product->addCategory($category);
        }
    }

    /**
     * Get variant groups and add them to the product
     *
     * @param int $count
     *
     */
    protected function addVariantGroups($product, $count)
    {
        $variantGroups = $this->variantGroupProvier->provideSeveral($count, $product);

        foreach ($variantGroups as $variantGroup) {
            $product->addGroup($variantGroup);
        }
    }

    /**
     * Get groups and add them to the product
     *
     * @param int $count
     *
     */
    protected function addGroups($product, $count)
    {
        $groups = $this->groupProvider->provideSeveral($count);

        foreach ($groups as $group) {
            $product->addGroup($group);
        }
    }
}
