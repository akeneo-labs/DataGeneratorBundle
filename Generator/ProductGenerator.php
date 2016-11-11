<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker;
use Pim\Bundle\DataGeneratorBundle\AttributeKeyProvider;
use Pim\Bundle\DataGeneratorBundle\Generator\Product\AbstractProductGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\Product\ProductRawBuilder;
use Pim\Bundle\DataGeneratorBundle\VariantGroupDataProvider;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Generate native CSV file for products
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductGenerator extends AbstractProductGenerator implements GeneratorInterface
{
    const TYPE = 'products';

    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var VariantGroupDataProvider[] */
    private $variantGroupDataProviders = [];

    /** @var AttributeKeyProvider */
    private $attributeKeyProvider;

    /**
     * @param ProductRawBuilder            $productRawBuilder
     * @param FamilyRepositoryInterface    $familyRepository
     * @param GroupRepositoryInterface     $groupRepository
     * @param AttributeKeyProvider         $attributeKeyProvider
     */
    public function __construct(
        ProductRawBuilder $productRawBuilder,
        FamilyRepositoryInterface $familyRepository,
        GroupRepositoryInterface $groupRepository,
        AttributeKeyProvider $attributeKeyProvider
    ) {
        parent::__construct($productRawBuilder, $familyRepository);
        $this->groupRepository = $groupRepository;
        $this->variantGroupDataProviders = [];
        $this->attributeKeyProvider = $attributeKeyProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $entitiesConfig, ProgressBar $progress, array $options = [])
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'data-gene');
        $outputFile = $globalConfig['output_dir'] . DIRECTORY_SEPARATOR . trim($entitiesConfig['filename']);

        $seed                = $globalConfig['seed'];
        $count               = (int) $entitiesConfig['count'];
        $nbAttrBase          = (int) $entitiesConfig['filled_attributes_count'];
        $nbAttrDeviation     = (int) $entitiesConfig['filled_attributes_standard_deviation'];
        $startIndex          = (int) $entitiesConfig['start_index'];
        $categoriesCount     = (int) $entitiesConfig['categories_count'];
        $variantGroupCount   = (int) $entitiesConfig['products_per_variant_group'];
        $mandatoryAttributes = $entitiesConfig['mandatory_attributes'];
        $forcedValues        = $entitiesConfig['force_values'];
        $delimiter           = $entitiesConfig['delimiter'];
        $percentageComplete  = $entitiesConfig['percentage_complete'];
        $allAttributeKeys    = $entitiesConfig['all_attribute_keys'];


        $faker = $this->initFaker($seed);

        for ($i = $startIndex; $i < ($startIndex + $count); $i++) {
            $isComplete = (bool)($faker->numberBetween(0, 100) < $percentageComplete);

            if (!$isComplete) {
                $product = $this->buildRawProduct(
                    $faker,
                    $forcedValues,
                    $mandatoryAttributes,
                    self::IDENTIFIER_PREFIX . $i,
                    $nbAttrBase,
                    $nbAttrDeviation,
                    $categoriesCount
                );
            } else {
                $product = $this->buildCompleteRawProduct(
                    $faker,
                    $forcedValues,
                    self::IDENTIFIER_PREFIX . $i,
                    $categoriesCount
                );
            }

            $this->bufferizeProduct($product, $tmpFile);
            $progress->advance();
        }

        if (true === $allAttributeKeys) {
            $keys = array_unique(array_merge($this->attributeKeyProvider->getAllAttributesKeys(), $this->headers));
            sort($keys);
            $this->headers = $keys;
        }

        $this->writeCsvFile($this->headers, $outputFile, $tmpFile, $delimiter);
        unlink($tmpFile);

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return self::TYPE === $type;
    }
}
