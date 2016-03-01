<?php

namespace Pim\Bundle\DataGeneratorBundle\Provider;

use Pim\Bundle\CatalogBundle\Repository\CategoryRepositoryInterface;
use Pim\Bundle\CatalogBundle\Faker\FakerFactoryInterface;

/**
 * Provides random categories from repository
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RandomCategoryProvider
{
    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var FakerFactoryInterface */
    protected $fakerFactory;

    /** @var array */
    protected $categories;

    /**
     * @param CategoryRepositoryInterface $groupRepository
     * @param FakerFactoryInterface       $fakerFactory
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        FakerFactoryInterface    $fakerFactory
    ) {
        $this->groupRepository = $groupRepository;
        $this->fakerFactory = $fakerFactory;
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

        return $faker->randomElements($this->getAllCategories(), $count);
    }

    /**
     * Return an array with all categories
     *
     * @return array
     */
    public function getAllCategories()
    {
        if (null === $this->categories) {
            $this->categories = $this->repository->findAll();
        }

        return $this->categories;
    }
}
