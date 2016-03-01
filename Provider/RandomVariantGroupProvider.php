<?php

namespace Pim\Bundle\DataGeneratorBundle\Provider;

use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\DataGeneratorBundle\Faker\FakerFactoryInterface;

/**
 * Provides random variant group from repository than can match
 * product attributes
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RandomVariantGroupProvider
{
    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /** @var FakerFactoryInterface */
    protected $fakerFactory;

    /** @var array */
    protected $variantGroups;

    /**
     * @param GroupRepositoryInterface $groupRepository
     * @param FakerFactoryInterface    $fakerFactory
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        FakerFactoryInterface    $fakerFactory
    ) {
        $this->groupRepository = $groupRepository;
        $this->fakerFactory    = $fakerFactory;
    }

    /**
     * Provide several random group repositories
     *
     * @param ProductInterface $product
     * @param int              $count
     *
     * @return GroupInterface[]
     */
    public function provideSeveral(ProductInterface $product, $count)
    {
        $faker = $fakerFactory->build();

        return $faker->randomElements($this->getCompatibleVariantGroups(), $count);
    }

    /**
     * Return variant groups that have attributes that belongs to the product
     *
     * @param ProductInterface $product
     */
    public function getCompatibleVariantGroups(ProductInterface $product)
    {
        // TODO

        return $this->getAllVariantGroups();
    }

    /**
     * Return an array with all variant groups
     *
     * @return array
     */
    public function getAllVariantGroups()
    {
        if (null === $this->variantGroups) {
            $this->variantGroups = $this->repository->getAllVariantGroups();
        }

        return $this->variantGroups;
    }
}
