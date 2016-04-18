<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker;
use Pim\Bundle\DataGeneratorBundle\Generator\Product\AbstractProductGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\Product\ProductRawBuilder;
use Pim\Bundle\DataGeneratorBundle\VariantGroupDataProvider;
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

    /**
     * @param ProductRawBuilder            $productRawBuilder
     * @param FamilyRepositoryInterface    $familyRepository
     * @param GroupRepositoryInterface     $groupRepository
     */
    public function __construct(
        ProductRawBuilder $productRawBuilder,
        FamilyRepositoryInterface $familyRepository,
        GroupRepositoryInterface $groupRepository
    ) {
        parent::__construct($productRawBuilder, $familyRepository);
        $this->groupRepository = $groupRepository;
        $this->variantGroupDataProviders = [];
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

        if ($variantGroupCount > 0) {
            foreach ($this->groupRepository->getAllVariantGroups() as $variantGroup) {
                $this->variantGroupDataProviders[] = new VariantGroupDataProvider($variantGroup, $variantGroupCount);
            }
        }

        if (count($this->variantGroupDataProviders) * $variantGroupCount > $count) {
            throw new \Exception(sprintf(
                'You require too much products per variant group (%s). '.
                'There is only %s variant groups for %s required products',
                $variantGroupCount,
                count($this->variantGroupDataProviders),
                $count
            ));
        }

        $faker = $this->initFaker($seed);

        for ($i = $startIndex; $i < ($startIndex + $count); $i++) {
            $isComplete = (bool)($faker->numberBetween(0, 100) < $percentageComplete);
            $variantGroupDataProvider = $this->getNextVariantGroupProvider($faker);
            $variantGroupAttributes = [];

            if (null !== $variantGroupDataProvider) {
                $variantGroupAttributes = $variantGroupDataProvider->getAttributes();
                $product['groups'] = $variantGroupDataProvider->getCode();
            }

            if (!$isComplete) {
                $product = $this->buildRawProduct(
                    $faker,
                    $forcedValues,
                    $mandatoryAttributes,
                    self::IDENTIFIER_PREFIX . $i,
                    $nbAttrBase - count($variantGroupAttributes),
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

            if (null !== $variantGroupDataProvider) {
                $product = array_merge($product, $variantGroupDataProvider->getData());
            }

            $this->bufferizeProduct($product, $tmpFile);
            $progress->advance();
        }

        $this->writeCsvFile($this->headers, $outputFile, $tmpFile, $delimiter);
        unlink($tmpFile);

        return [];
    }

    /**
     * Get a random variantGroupProvider. If this is the last usage of it, removes it from the list.
     * If there is no remaining VariantGroupProvider, returns null.
     *
     * @param Faker\Generator $faker
     *
     * @return null|VariantGroupDataProvider
     */
    private function getNextVariantGroupProvider(Faker\Generator $faker)
    {
        $variantGroupProvider = null;

        if (count($this->variantGroupDataProviders) > 0) {
            $variantGroupProviderIndex = $faker->numberBetween(0, count($this->variantGroupDataProviders) - 1);
            $variantGroupProvider = $this->variantGroupDataProviders[$variantGroupProviderIndex];

            if ($variantGroupProvider->isLastUsage()) {
                array_splice($this->variantGroupDataProviders, $variantGroupProviderIndex, 1);
            }
        }

        return $variantGroupProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return self::TYPE === $type;
    }
}
