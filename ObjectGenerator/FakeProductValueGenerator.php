<?php

namespace Pim\Bundle\DataGeneratorBundle\ObjectGenerator;

use Pim\Bundle\DataGeneratorBundle\ObjectGenerator\ProductValueData\DataGeneratorRegistry;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * Generate fake product value object
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FakeProductValueGenerator
{
    /** @staticvar DataGeneratorRegistry */
    protected $generatorRegistry;

    /** @staticvar ProductBuilderInterface */
    protected $productBuilder;

    /**
     * @param DataGeneratorRegistry $generatorRegistry
     * @param ProductBuilder        $productBuilder
     */
    public function __construct(
        DataGeneratorRegistry $generatorRegistry,
        ProductBuilderInterface $productBuilder
    ) {
        $this->generatorRegistry = $generatorRegistry;
        $this->productBuilder    = $productBuilder;
    }

    /**
     * Generate a fake product value based on the provided arguments
     *
     * @param AttributeInterface $attribute
     * @param LocaleInterface    $locale
     * @param ChannelInterface   $channel
     *
     * @return ProductValueInterface
     */
    public function generateProductValue(
        AttributeInterface $attribute,
        LocaleInterface $locale = null,
        ChannelInterface $channel = null
    ) {
        if (null !== $locale && !$attribute->isLocalizable()) {
            throw new \LogicException("Locale provided, but the provided attribute is not localizable.");
        }
        if (null !== $channel && !$attribute->isScopable()) {
            throw new \LogicException("Channel provided, but the provided attribute is not scopable.");
        }

        $value = $this->productBuilder->createProductValue($attribute, $locale, $channel);

        $dataGenerator = $this->generatorRegistry->getDataGenerator($attribute);

        if (null === $dataGenerator) {
            throw new \LogicException(
                sprintf(
                    "Unable to find a data generator for attribute %s",
                    $attribute->getCode()
                )
            );
        }

        $value->setData($dataGenerator->generateData($attribute));

        return $value;
    }
}
