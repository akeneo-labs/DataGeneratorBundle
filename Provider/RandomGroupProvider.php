<?php

namespace Pim\Bundle\DataGeneratorBundle\Provider;

use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\DataGeneratorBundle\Faker\FakerFactoryInterface;

/**
 * Provides random group from repository than can match
 * product attributes
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RandomGroupProvider
{
    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /** @var FakerFactoryInterface */
    protected $fakerFactory;

    /** @var array */
    protected $groups;

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
     * @param int              $count
     *
     * @return GroupInterface[]
     */
    public function provideSeveral($count)
    {
        $faker = $fakerFactory->build();

        return $faker->randomElements($this->geAllGroups(), $count);
    }

    /**
     * Return an array with all groups
     *
     * @return array
     */
    public function getAllGroups()
    {
        if (null === $this->groups) {
            $this->groups = $this->repository->getAllGroupsExceptVariant();
        }

        return $this->groups;
    }
}
