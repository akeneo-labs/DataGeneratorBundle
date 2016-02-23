<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\DataGeneratorBundle\Generator\Product\ProductValueBuilder;
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;
use Symfony\Component\Console\Helper\ProgressHelper;

class ProductDraftGenerator
{
    /** @var ProductValueBuilder */
    private $valueBuilder;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

//    /** @var AttributeInterface[] */
//    private $attributes;


    public function __construct(
        ProductValueBuilder $valueBuilder,
        AttributeRepositoryInterface $attributeRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->valueBuilder = $valueBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressHelper $progress, array $options = [])
    {
        $productFile = $this->getCsvProductFilename($globalConfig, $config);
        $reader      = ReaderFactory::create(Type::CSV);
        $reader->setFieldDelimiter(';');
        $reader->open($productFile);

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $product) {
                foreach ($this->userRepository->findAll() as $user) {
                    $updatedRow = $this->setRandomValuesToProduct($product, $config);
                }
            }
        }

        $reader->close();
    }

    /**
     * @param array $config
     *
     * @return array
     */
    private function getLockedValuesCodes(array $config)
    {
        $lockedValues[] = $this->attributeRepository->getIdentifierCode();
        if (isset($config['products']['force_values'])) {
            $lockedValues = array_merge(
                $lockedValues,
                array_keys($config['products']['force_values'])
            );
        }

        return $lockedValues;
    }

    /**
     * @param array $globalConfig
     * @param array $config
     *
     * @return string
     */
    private function getCsvProductFilename(array $globalConfig, array $config)
    {
        if (!empty($config['products']['filename'])) {
            return $globalConfig['output_dir'] . DIRECTORY_SEPARATOR . trim($config['products']['filename']);
        }

        return $globalConfig['output_dir'] . DIRECTORY_SEPARATOR . ProductGenerator::DEFAULT_FILENAME;
    }

    /**
     * @param array $product
     * @param array $config
     *
     * @return array
     */
    private function setRandomValuesToProduct(array $product, array $config)
    {
        $lockedValues = $this->getLockedValuesCodes($config);

        foreach ($product as $code => $value) {
            if (!in_array($code, $lockedValues)) {
                $newValue = $this->valueBuilder->build(
                    $this->attributeRepository->findOneByIdentifier($code)
                );
                $product[$code] = $newValue;
            }
        }

       return $product;
    }
}
