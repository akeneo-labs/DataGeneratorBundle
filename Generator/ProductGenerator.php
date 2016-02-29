<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;

/**
 * Generate products objects
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductGenerator
{
    protected $productBuilder;

    public function __construct(ProductBuilderInterface $productBuilder)
    {
        $this->productBuilder = $productBuilder;
    }
    /**
     * Generate a product with an identifier based on the provided number
     *
     * @param int $number
     */
    public function generate($number)
    {
        return $this->productBuilder->createProduct("sku-".$number);
    }
}
