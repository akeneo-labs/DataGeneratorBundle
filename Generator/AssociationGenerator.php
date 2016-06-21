<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Faker\Factory;
use Faker\Generator;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AssociationTypeRepository;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Generator of a products file with associations only
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationGenerator implements GeneratorInterface
{
    const TYPE = 'associations';

    const DEFAULT_DELIMITER = ';';

    /** @var ProductQueryBuilderFactoryInterface */
    protected $productQueryBuilderFactory;

    /** @var AssociationTypeRepository */
    protected $associationTypeRepository;

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
     * @param AssociationTypeRepository  $associationTypeRepository
     * @param ObjectDetacherInterface    $objectDetacherInterface
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        AssociationTypeRepository $associationTypeRepository,
        ObjectDetacherInterface $objectDetacherInterface
    ) {
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->associationTypeRepository = $associationTypeRepository;
        $this->headers = [];
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $entitiesConfig, ProgressBar $progress, array $options = [])
    {
        $associationsCount = (int) $entitiesConfig['associations_per_product'];
        $productQueryBuilder = $this->productQueryBuilderFactory->create();
        $products = $productQueryBuilder->execute();

        if (0 == $products->count()) {
            throw new \LogicException('No products found in the PIM to generate associations');
        }

        $this->faker = Factory::create();
        if (isset($globalConfig['seed'])) {
            $this->faker->seed($globalConfig['seed']);
        }

        $associations = [];

        foreach ($products as $product) {
            $association['sku'] = $product->getIdentifier()->getData();
            $associationType = $this->getRandomAssociation();

            $associations[$associationType] = $this->fillInRandomAssociatedProducts($product, $associationsCount);
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return self::TYPE === $type;
    }

    /**
     * Bufferize the product for latter use and set the headers
     *
     * @param array  $product
     * @param string $tmpFile
     */
    protected function bufferizeAssociation(array $product, $tmpFile)
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
    protected function writeCsvFile(array $headers, $outputFile, $tmpFile, $delimiter)
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

    /**
     * @return AssociationTypeInterface
     */
    private function getRandomAssociation()
    {
        return $this->faker->randomElements($this->associationTypeRepository->findAll());
    }

    /**
     * Find product skus different from the product passed as parameter
     *
     * @param $excludedProduct
     * @param $associationCount
     *
     * @return array
     */
    private function fillInRandomAssociatedProducts($excludedProduct, $associationCount)
    {
        $i = 0;
        $productIdentifiers = [];

        $productQueryBuilder = $this->productQueryBuilderFactory->create();
        $pqb = $productQueryBuilder->getQueryBuilder();
        $products = $pqb->getQuery();

        foreach ($products as $product) {
            if ($i >= $associationCount) {
                break;
            }

            if ($excludedProduct->getIdentifier()->getData() !== $product->getIdentifier->getData()) {
                $productIdentifiers[] = $product->getIdentifier()->getData();
                $i++;
            }

            $this->objectDetacher->detach($product);
        }

        return join(',', $productIdentifiers);
    }
}
