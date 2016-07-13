<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Faker\Factory;
use Faker\Generator;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\Console\Helper\ProgressHelper;

/**
 * Generator of a product file with product and group associations solely
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationGenerator implements GeneratorInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $productQueryBuilderFactory;

    /** @var AssociationTypeRepositoryInterface */
    protected $associationTypeRepository;

    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var Generator */
    protected $faker;

    /** @var array */
    protected $headers;

    /**
     * AssociationGenerator constructor.
     *
     * @param ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
     * @param AssociationTypeRepositoryInterface  $associationTypeRepository
     * @param GroupRepositoryInterface            $groupRepository
     * @param AttributeRepositoryInterface        $attributeRepository
     * @param ObjectDetacherInterface             $objectDetacher
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        AssociationTypeRepositoryInterface $associationTypeRepository,
        GroupRepositoryInterface $groupRepository,
        AttributeRepositoryInterface $attributeRepository,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->associationTypeRepository = $associationTypeRepository;
        $this->groupRepository = $groupRepository;
        $this->attributeRepository = $attributeRepository;
        $this->objectDetacher = $objectDetacher;
        $this->headers = [];
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $generatorConfig, ProgressHelper $progress, array $options = [])
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'data-gene');
        $outputFile = $globalConfig['output_dir'] . DIRECTORY_SEPARATOR . trim($generatorConfig['filename']);
        $delimiter = $generatorConfig['delimiter'];

        $associationsProductCount = $generatorConfig['product_associations_per_product'];
        $associationsVariantGroupCount = $generatorConfig['group_associations_per_product'];
        $associationLimitNumber = (isset($generatorConfig['products_to_process_limit'])) ? $generatorConfig['products_to_process_limit'] : null;
        $identifierCode = $this->attributeRepository->getIdentifierCode();

        $products = $this->getProducts();
        $productsToAssociate = $this->getProducts();

        if (0 === $this->associationTypeRepository->countAll()) {
            throw new \LogicException('No association type found to generate product associations');
        }

        if (0 === $products->count()) {
            throw new \LogicException('No products found in the PIM to generate associations');
        }

        if (0 === $this->groupRepository->countVariantGroups() && $associationsVariantGroupCount > 0) {
            throw new \LogicException('No variant groups found to generate group associations');
        }

        $this->faker = Factory::create();
        if (isset($globalConfig['seed'])) {
            $this->faker->seed($globalConfig['seed']);
        }

        foreach ($products as $productIndex => $product) {
            if (null !== $associationLimitNumber && $productIndex >= $associationLimitNumber) {
                break;
            }

            $association = [];
            $association[$identifierCode] = $product->getIdentifier()->getData();

            $associationTypesProduct = $this->getRandomAssociation('-products');
            $randomProducts = $this->generateRandomProductIdentifiers(
                $product,
                $productsToAssociate,
                $associationsProductCount
            );
            if (0 !== count($randomProducts)) {
                $association[$associationTypesProduct] = join(',', $randomProducts);
            }

            $associationTypeGroup = $this->getRandomAssociation('-groups');
            $randomGroups = $this->generateRandomVariantGroupIdentifiers(
                $associationsVariantGroupCount
            );
            if (0 !== count($randomGroups)) {
                $association[$associationTypeGroup] = join(',', $randomGroups);
            }

            $this->bufferizeAssociation($association, $tmpFile);

            $this->objectDetacher->detach($product);

            $progress->advance();
        }

        $this->writeCsvFile($this->headers, $outputFile, $tmpFile, $delimiter);
        unlink($tmpFile);

        return $this;
    }

    /**
     * Generates a normalised association type code
     *
     * @param $typeOfAssociation string
     *
     * @return string
     */
    protected function getRandomAssociation($typeOfAssociation)
    {
        $associationType = $this->faker->randomElement($this->associationTypeRepository->findAll());
        return $associationType->getCode() . $typeOfAssociation;
    }

    /**
     * Find randomly product identifiers different from the product passed as parameter
     *
     * @param ProductInterface $excludedProduct
     * @param CursorInterface  $products
     * @param int              $associationCount
     *
     * @return array
     */
    protected function generateRandomProductIdentifiers($excludedProduct, CursorInterface $products, $associationCount)
    {
        $productIdentifiers = [];
        $i = 0;

        while ($i < $associationCount) {
            if (!$products->valid()) {
                $products->rewind();
                $products->next();
            }

            $product = $products->current();

            if ($excludedProduct->getIdentifier()->getData() !== $product->getIdentifier()->getData()) {
                $productIdentifiers[] = $product->getIdentifier()->getData();
                $i++;
            }

            $products->next();

            $this->objectDetacher->detach($product);
        }

        return $productIdentifiers;
    }

    /**
     * Generate a comma separated list of random variant groups codes
     *
     * @param $associationsVariantGroupCount
     *
     * @return array
     */
    private function generateRandomVariantGroupIdentifiers($associationsVariantGroupCount)
    {
        if (0 === $associationsVariantGroupCount) {
            return [];
        }

        $variantGroups = $this->faker->randomElements(
            $this->groupRepository->getAllVariantGroups(),
            $associationsVariantGroupCount
        );

        $variantGroupCodes = array_map(function ($variantGroup) {
            return $variantGroup->getCode();
        }, $variantGroups);

        return $variantGroupCodes;
    }

    /**
     * @return CursorInterface
     */
    private function getProducts()
    {
        $productQueryBuilder = $this->productQueryBuilderFactory->create();
        $products = $productQueryBuilder->execute();

        return $products;
    }

    /**
     * Bufferize the product for latter use and set the headers
     *
     * @param array  $product
     * @param string $tmpFile
     */
    private function bufferizeAssociation(array $product, $tmpFile)
    {
        $this->headers = array_unique(array_merge($this->headers, array_keys($product)));

        file_put_contents($tmpFile, serialize($product) . "\n", FILE_APPEND);
    }

    /**
     * Write the CSV file from data coming from the buffer
     *
     * @param array  $headers
     * @param string $outputFile
     * @param string $tmpFile
     * @param string $delimiter
     */
    private function writeCsvFile(array $headers, $outputFile, $tmpFile, $delimiter)
    {
        $buffer = fopen($tmpFile, 'r');

        $csvFile = fopen($outputFile, 'w');

        fputcsv($csvFile, $headers, $delimiter);
        $headersAsKeys = array_fill_keys($headers, "");

        while ($bufferedProduct = fgets($buffer)) {
            $product     = unserialize($bufferedProduct);
            $productData = array_merge($headersAsKeys, $product);
            fputcsv($csvFile, $productData, $delimiter);
        }
        fclose($csvFile);
        fclose($buffer);
    }
}
