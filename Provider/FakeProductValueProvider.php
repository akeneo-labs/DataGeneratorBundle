<?php

namespace Pim\Bundle\DataGeneratorBundle\Provider;

use Pim\Bundle\DataGeneratorBundle\ObjectGenerator\Fake\FakeProductValueGenerator;

/**
 * Provides fake products values depending on configuration
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FakeProductValueProvider
{
    /** @var FakeProductValueGenerator */
    protected $valueGenerator;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var FakerFactoryInterface */
    protected $fakerFactory;

    /**
     * @param FakeProductValueGenerator  $valueGenerator
     * @param ChannelRepositoryInterface $channelRepository
     * @param LocaleRepositoryInterface  $localeRepository
     * @param FakerFactoryInterface      $fakerFactory
     */
    public function __construct(
        FakeProductValueGenerator $valueGenerator,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryinterface $localeRepository,
        FakerFactoryInterface $fakerFactory
    ) {
        $this->channelRepository = $channelRepository;
        $this->localeRepository  = $localeRepository;
        $this->valueGenerator    = $valueGenerator;
        $this->fakerFactory      = $fakerFactory;
    }

    /**
     * Provide several fake product values from family based on configuration and completeness
     * options
     *
     * @param FamilyInterface $family
     * @param int             $count
     * @param boolean         $complete
     *
     * @return ProductValueInterface[]
     */
    public function provideSeveral(
        FamilyInterface $family,
        $count,
        $complete
    ) {
        $valuesSpecs = $this->getRandomValuesSpecs($famiy, $count, $complete);

        $values = [];

        foreach ($valuesSpecs as $valueSpecs) {
            list($attribute, $locale, $channel) = $valueSpecs;
            $values = $this->valueGenerator->generateProductValue($attribute, $locale, $channel);
        }

        return $values;
    }

    /**
     * Pick attributes and generate values specs, i.e. attribute, locale and channel.
     * Make sure the provided values cover the completeness requirements if needed.
     *
     * @param FamilyInterface $family
     * @param int             $count
     * @param boolean         $complete
     *
     * @return array
     */
    protected function getRandomValuesSpecs(FamilyInterface $family, $count, $complete)
    {
        $faker = $this->fakerFactory->create();

        $allValuesSpecs = $this->getAllValuesSpecs($family);

        //TODO implements complete
        return $faker->randomElements($allValuesSpecs, $count);
    }

    /**
     * Generate all possible values specs from a family
     *
     * @param FamilyInterface $family
     *
     * @return array
     */
    protected function getAllValuesSpecs(FamilyInterface $family)
    {
        $valuesSpecs = [];

        foreach ($family->getAttributes as $attribute)
        {
            if ('pim_catalog_identifier' === $attribute->getAttributeType()) {
                continue;
            }

            if (!$attribute->isScopable() && !$attribute->isLocalizable()) {
                $valuesSpecs[] = [$attribute, null, null];
            }

            if (!$attribute->isScopable() && $attribute->isLocalizable()) {
                foreach ($this->localeRepository->getActivatedLocales() as $locale) {
                    $valuesSpecs[] = [$attribute, $locale, null];
                }
            }

            if ($attribute->isScopable() && !$attribute->isLocalizable()) {
                foreach ($this->channelRepository->findAll() as $channel) {
                    $valuesSpecs[] = [$attribute, null, $channel];
                }
            }

            if ($attribute->isScopable() && $attribute->isLocalizable()) {
                foreach ($this->channelRepository->findAll() as $channel) {
                    foreach ($channel->getLocales() as $locale) {
                        $valuesSpecs[] = [$attribute, $locale, $channel];
                    }
                }
            }
        }

        return $valuesSpecs;
    }
}
