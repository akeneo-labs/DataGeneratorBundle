<?php


namespace Pim\Bundle\DataGeneratorBundle\Generator\Product;

use Doctrine\Common\Persistence\ObjectRepository;
use Faker;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;

/**
 * Abstract generator for product and drafts
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AbstractProductGenerator
{
    const IDENTIFIER_PREFIX = 'id-';
    const DEFAULT_DELIMITER = ';';

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var ProductRawBuilder */
    protected $productRawBuilder;

    /** @var FamilyInterface[] */
    protected $families;

    /** @var array */
    protected $headers;

    /**
     * AbstractProductGenerator constructor.
     *
     * @param ProductRawBuilder         $productRawBuilder
     * @param FamilyRepositoryInterface $familyRepository
     */
    public function __construct(ProductRawBuilder $productRawBuilder, FamilyRepositoryInterface $familyRepository)
    {
        $this->productRawBuilder = $productRawBuilder;
        $this->familyRepository = $familyRepository;
        $this->headers = [];
    }

    /**
     * @param Faker\Generator $faker
     * @param array           $forcedValues
     * @param array           $mandatoryAttributes
     * @param int             $id
     * @param int             $nbAttr
     * @param int             $nbAttrDeviation
     * @param int             $nbCategories
     *
     * @return array
     */
    protected function buildRawProduct(
        Faker\Generator $faker,
        array $forcedValues,
        array $mandatoryAttributes,
        $id,
        $nbAttr,
        $nbAttrDeviation,
        $nbCategories
    ) {
        $family = $this->getRandomFamily($faker);
        $product = $this->productRawBuilder->buildBaseProduct($family, $id, '');

        $this->productRawBuilder->fillInRandomCategories($product, $nbCategories);
        $this->productRawBuilder->fillInRandomAttributes($family, $product, $forcedValues, $nbAttr, $nbAttrDeviation);
        $this->productRawBuilder->fillInMandatoryAttributes($family, $product, $forcedValues, $mandatoryAttributes);

        return $product;
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
     * Bufferize the product for latter use and set the headers
     *
     * @param array  $product
     * @param string $tmpFile
     */
    protected function bufferizeProduct(array $product, $tmpFile)
    {
        $this->headers = array_unique(array_merge($this->headers, array_keys($product)));

        file_put_contents($tmpFile, serialize($product) . "\n", FILE_APPEND);
    }

    /**
     * Get a random item from a repo
     *
     * @param Faker\Generator  $faker
     * @param ObjectRepository $repo
     * @param array            &$items
     *
     * @return mixed
     */
    protected function getRandomItem(Faker\Generator $faker, ObjectRepository $repo, array &$items = null)
    {
        if (null === $items) {
            $items       = [];
            $loadedItems = $repo->findAll();
            foreach ($loadedItems as $item) {
                $items[$item->getCode()] = $item;
            }
        }

        return $faker->randomElement($items);
    }

    /**
     * @param string $seed
     *
     * @return Faker\Generator
     */
    protected function initFaker($seed)
    {
        $faker = Faker\Factory::create();
        $faker->seed($seed);
        $this->productRawBuilder->setFakerGenerator($faker);

        return $faker;
    }

    /**
     * Get a random family
     *
     * @param Faker\Generator $faker
     *
     * @return FamilyInterface
     */
    private function getRandomFamily(Faker\Generator $faker)
    {
        return $this->getRandomItem($faker, $this->familyRepository, $this->families);
    }
}
