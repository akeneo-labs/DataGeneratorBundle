<?php

namespace Pim\Bundle\DataGeneratorBundle\Reader;

use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Faker;
use Pim\Bundle\DataGeneratorBundle\Generator\ProductGenerator;

/**
 * Read generated products
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GeneratedProductReader implements ItemReaderInterface
{
    /** @var ProductGenerator */
    protected $productGenerator;

    /** @var int */
    protected $itemCount;

    /** var int */
    protected $itemNumber;

    /**
     * @param ProductGenerator $productGenerator
     */
    public function __construct(ProductGenerator $productGenerator) {
        $this->productGenerator = $productGenerator;
        $this->itemNumber = 0;
    }

    /**
     * Set the item count to generate
     *
     * @param int $itemCount
     */
    public function setItemCount($itemCount)
    {
        $this->itemCount = $itemCount;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $product = null;
        echo "DEBUG reader->read\n";
        if ($this->itemNumber < $this->itemCount) {
            echo "DEBUG reader->read one\n";
            $product = $this->productGenerator->generate($this->itemNumber);
            echo "DEBUG reader->read fterne\n";
        }

        $this->itemNumber++;

        return $product;
    }
}
