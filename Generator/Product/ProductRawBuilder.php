<?php


namespace Pim\Bundle\DataGeneratorBundle\Generator\Product;

use Faker;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;

/**
 * Build a raw product (ie: as an array) with random data.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRawBuilder
{
    /** @var Faker\Generator */
    private $faker;

    /** @var ProductValueBuilder */
    private $valueBuilder;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var array */
    private $attributesByFamily;

    public function __construct(ProductValueBuilder $valueBuilder, AttributeRepositoryInterface $attributeRepository)
    {
        $this->valueBuilder = $valueBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->attributesByFamily = [];
    }

    /**
     * @param Faker\Generator $faker
     */
    public function setFakerGenerator(Faker\Generator $faker)
    {
        $this->faker = $faker;
    }

    /**
     * Modify the $product to fill some random properties
     *
     * @param FamilyInterface $family
     * @param array           $product
     * @param array           $forcedProperties
     * @param int             $nbAttr
     * @param int             $nbAttrDeviation
     */
    public function fillInRandomProperties(
        FamilyInterface $family,
        array &$product,
        array $forcedProperties,
        $nbAttr,
        $nbAttrDeviation
    ) {
        $randomNbAttr = $this->getRandomAttributesCount(
            $family,
            $nbAttr,
            $nbAttrDeviation
        );
        $attributes = $this->getRandomAttributesFromFamily($family, $randomNbAttr);

        foreach ($attributes as $attribute) {
            $valueData = $this->generateValue($attribute, $forcedProperties);
            $product   = array_merge($product, $valueData);
        }
    }

    /**
     * Modify the $product to fill in its mandatory properties.
     *
     * @param FamilyInterface $family
     * @param array           $product
     * @param array           $forcedProperties
     * @param array           $mandatoryProperties
     */
    public function fillInMandatoryProperties(
        FamilyInterface $family,
        array &$product,
        array $forcedProperties,
        array $mandatoryProperties
    ) {
        foreach ($mandatoryProperties as $property) {
            if (isset($this->attributesByFamily[$family->getCode()][$property])) {
                $attribute = $this->attributesByFamily[$family->getCode()][$property];
                $valueData = $this->generateValue($attribute, $forcedProperties);
                $product   = array_merge($product, $valueData);
            }
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $forceProperties
     *
     * @return array
     */
    private function generateValue(AttributeInterface $attribute, array $forceProperties)
    {
        if (isset($forceProperties[$attribute->getCode()])) {
            return [$attribute->getCode() => $forceProperties[$attribute->getCode()]];
        }

        return $this->valueBuilder->build($attribute);
    }

    /**
     * Get random attributes from the family
     *
     * @param FamilyInterface $family
     * @param int             $count
     *
     * @return AttributeInterface[]
     */
    private function getRandomAttributesFromFamily(FamilyInterface $family, $count)
    {
        return $this->faker->randomElements($this->getAttributesFromFamily($family), $count);
    }

    /**
     * Returns the number of attributes to set.
     *
     * @param FamilyInterface $family
     * @param int             $nbAttrBase
     * @param int             $nbAttrDeviation
     *
     * @return int
     */
    private function getRandomAttributesCount(FamilyInterface $family, $nbAttrBase, $nbAttrDeviation)
    {
        if ($nbAttrBase > 0) {
            if ($nbAttrDeviation > 0) {
                $nbAttr = $this->faker->numberBetween(
                    $nbAttrBase - round($nbAttrDeviation / 2),
                    $nbAttrBase + round($nbAttrDeviation / 2)
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
     * Get non-identifier attribute from family
     *
     * @param FamilyInterface $family
     *
     * @return AttributeInterface[]
     */
    private function getAttributesFromFamily(FamilyInterface $family)
    {
        $familyCode = $family->getCode();

        if (!isset($this->attributesByFamily[$familyCode])) {
            $this->attributesByFamily[$familyCode] = [];

            $attributes = $family->getAttributes();
            foreach ($attributes as $attribute) {
                if ($attribute->getCode() !== $this->attributeRepository->getIdentifierCode()) {
                    $this->attributesByFamily[$familyCode][$attribute->getCode()] = $attribute;
                }
            }
        }

        return $this->attributesByFamily[$familyCode];
    }
}
