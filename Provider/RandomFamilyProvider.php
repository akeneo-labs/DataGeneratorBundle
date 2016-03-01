<?php

namespace Pim\Bundle\DataGeneratorBundle\Provider;

use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;

/**
 * Provides random family from the one provided by the repository
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RandomFamilyProvider
{
    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /** @var SeededFakerFactory */
    protected $fakerFactory;

    /** @var array */
    protected $families;

    /**
     * @param FamilyRepositoryInterface $familyRepository
     */
    public function __construct(
        FamilyRepository $familyRepository
    ) {
        $this->familyRepository = $familyRepository;
    }

    /**
     * Provide one random family
     *
     * @return FamilyInterface
     */
    public function provideOne()
    {
        $faker = $fakerFactory->build();

        return $faker->randomElement($this->getAllFamilies());
    }

    /**
     * Return an array with all families from the repo
     *
     * @return array
     */
    public function getAllFamilies()
    {
        if (null === $this->families) {
            $this->families = $this->repository->findAll();
        }

        return $this->families;
    }
}
