<?php

namespace Pim\Bundle\DataGeneratorBundle\Faker\Provider;

use Faker\Provider\Base;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Generate fake Akeneo Product Value
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoPimProductValue extends Base
{
    /**
     * Get a fake product value from an attribute
     *
     * @param AbstractAttribute $attribute
     * @param string $scope
     * @param string $locale
     *
     * @return ProductValue
     */
    public function akeneoPimProductValue(
        AbstractAttribute $attribute,
        $locale = null,
        $scope = null
    ) {
        $value = new ProductValue();
        $value->setAttribute($attribute);

        return $value;
    }
}
