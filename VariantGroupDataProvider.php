<?php

namespace Pim\Bundle\DataGeneratorBundle;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * The VariantGroupDataProvider provides data for the product generator.
 * It provides a different values combination for every product belonging to a variant group.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupDataProvider
{
    /** @var GroupInterface */
    protected $variantGroup;

    /** @var int */
    protected $remainingCount;

    /** @var array */
    protected $attributeValues;

    /**
     * @param GroupInterface $variantGroup
     * @param int            $remainingCount
     *
     * @throws Exception
     */
    public function __construct(GroupInterface $variantGroup, $remainingCount)
    {
        $this->variantGroup    = $variantGroup;
        $this->remainingCount  = $remainingCount;
        $this->attributeValues = [];

        $availableCombinations = 1;
        /** @var AttributeInterface $attribute */
        foreach ($variantGroup->getAxisAttributes() as $attribute) {
            $attributeCode = $attribute->getCode();
            $this->attributeValues[$attributeCode] = [];

            /** @var AttributeOptionInterface $option */
            foreach ($attribute->getOptions() as $option) {
                $this->attributeValues[$attributeCode][] = $option->getCode();
            }
            $availableCombinations *= count($this->attributeValues[$attributeCode]);
        }

        if ($availableCombinations < $remainingCount) {
            throw new Exception(sprintf(
                'Variant group %s have only %s value combinations, %s are needed.',
                $variantGroup->getLabel(),
                $availableCombinations,
                $remainingCount
            ));
        }
    }

    /**
     * Returns the data for every attribute from the current index.
     * For example, with 3 attributes, each having 2 attribute values (0 and 1), it returns:
     *
     * +-------+---------+---------+---------+
     * | index | index_1 | index_2 | index_3 |
     * +-------+---------+---------+---------+
     * | 0     | 0       | 0       | 0       |
     * | 1     | 1       | 0       | 0       |
     * | 2     | 0       | 1       | 0       |
     * | 3     | 1       | 1       | 0       |
     * | 4     | 0       | 0       | 1 ...   |
     * +-------+---------+---------+---------+
     *
     * @return array
     */
    public function getData()
    {
        $attributeCodes = array_keys($this->attributeValues);
        $data = [];
        $multiplier = 1;

        for ($i = 0; $i < count($attributeCodes); $i++) {
            $attributeCode = $attributeCodes[$i];
            $valuesCount = count($this->attributeValues[$attributeCode]);
            $valueIndex = floor($this->remainingCount/$multiplier) % $valuesCount;

            $data[$attributeCode] = $this->attributeValues[$attributeCode][$valueIndex];
            $multiplier *= $valuesCount;
        }

        $this->remainingCount--;

        return $data;
    }

    public function isLastUsage()
    {
        return ($this->remainingCount <= 1);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->variantGroup->getCode();
    }

    /**
     * @return ArrayCollection
     */
    public function getAttributes()
    {
        return $this->variantGroup->getAxisAttributes();
    }
}
