<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker;
use Pim\Bundle\DataGeneratorBundle\Generator\Product\AbstractProductGenerator;
use Symfony\Component\Console\Helper\ProgressHelper;


/**
 * Generate native CSV file for product drafts
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductDraftGenerator extends AbstractProductGenerator implements GeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $entitiesConfig, ProgressHelper $progress, array $options = [])
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'data-gene');
        $outputFile = $globalConfig['output_dir'] . DIRECTORY_SEPARATOR . trim($entitiesConfig['filename']);

        $seed                = $globalConfig['seed'];
        $count               = (int) $entitiesConfig['count'];
        $nbAttrBase          = (int) $entitiesConfig['filled_attributes_count'];
        $nbAttrDeviation     = (int) $entitiesConfig['filled_attributes_standard_deviation'];
        $startIndex          = (int) $entitiesConfig['start_index'];
        $mandatoryAttributes = $entitiesConfig['mandatory_attributes'];
        $forcedValues        = $entitiesConfig['force_values'];
        $delimiter           = $entitiesConfig['delimiter'];

        $faker = $this->initFaker($seed);

        for ($i = $startIndex; $i < ($startIndex + $count); $i++) {

            $product = $this->buildRawProduct(
                $faker,
                $forcedValues,
                $mandatoryAttributes,
                self::IDENTIFIER_PREFIX . $i,
                $nbAttrBase,
                $nbAttrDeviation,
                0
            );

            $this->bufferizeProduct($product, $tmpFile);
            $progress->advance();
        }

        $this->writeCsvFile($this->headers, $outputFile, $tmpFile, $delimiter);
        unlink($tmpFile);

        return $this;
    }
}
