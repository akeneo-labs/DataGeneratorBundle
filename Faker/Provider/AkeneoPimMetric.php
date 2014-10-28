<?php

namespace Pim\Bundle\DataGeneratorBundle\Faker\Provider;

use Faker\Provider\Base;
use Pim\Bundle\CatalogBundle\Model\Metric;

/**
 * Generate fake Akeneo Metric
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoPimMetric extends Base
{
    /**
     * Get a fake metric for a family
     *
     * @param string $family
     * @param string $unit
     * @param int    $nbMaxDecimals
     *
     * @return Metric
     */
    public function akeneoPimMetric($family, $unit, $nbMaxDecimals = 2)
    {
        $metric = new Metric();
        $metric->setFamily($family);
        $metric->setUnit($unit);

        $metric->setData($this->randomFloat($nbMaxDecimals));

        return $metric;
    }
}
