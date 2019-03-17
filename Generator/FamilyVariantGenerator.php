<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Faker\Factory;
use Faker\Generator;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Generate native YML file for family variant useable as fixtures
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantGenerator implements GeneratorInterface
{
    // code;family;label-de_DE;label-en_US;label-fr_FR;variant-axes_1;variant-axes_2;variant-attributes_1;variant-attributes_2

    const TYPE = 'family_variant';

    const FAMILY_VARIANT_CODE_PREFIX = 'fam_variant_';

    const FAMILY_VARIANTS_FILENAME = 'family_variants.csv';

    const ATTRIBUTE_DELIMITER = ',';

    /** @var array */
    protected $locales = [];

    /** @var array */
    protected $channels = [];

    /** @var Generator */
    protected $faker;

    /** @var array */
    protected $familyVariants = [];

    /** @var AttributeInterface[] */
    protected $availableAxes = [];

    /** @var AttributeInterface[] */
    protected $availableAttributes = [];

    /** @var string */
    protected $identifierAttributeCode;

    /** @var array */
    protected $availableFamilies;

    /** @var CsvWriter */
    protected $writer;

    /**
     * @param CsvWriter $writer
     */
    public function __construct(CsvWriter $writer)
    {
        $this->writer = $writer;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $entitiesConfig, ProgressBar $progress, array $options = [])
    {
        $this->locales  = $options['locales'];
        $this->channels = $options['channels'];

        $count                         = (int) $entitiesConfig['count'];
        $oneLevelProbability           = (float) $entitiesConfig['one_level_probability'];
        $twoLevelProbability           = (float) $entitiesConfig['two_level_probability'];
        $maxAttributesPerAxe           = (int) $entitiesConfig['max_attributes_per_axe'];
        $this->identifierAttributeCode = $entitiesConfig['identifier_attribute'];
        $this->setFamilies($options['families'], $count);
        $this->setFaker($globalConfig);

        if (100 !== $oneLevelProbability + $twoLevelProbability) {
            throw new \Exception('Addition of one level and two level family variants probability must be equals to 100');
        }
        $nbOfOneLevelNeeded = $count * 100 / $oneLevelProbability;
        $nbOfTwoLevelNeeded = $count - $nbOfOneLevelNeeded;

        $familyVariants = [];
        // code;family;label-de_DE;label-en_US;label-fr_FR;variant-axes_1;variant-axes_2;variant-attributes_1;variant-attributes_2
        foreach ($this->availableFamilies as $code => $parentFamily) {
            $family = [];

            $family['code']   = self::FAMILY_VARIANT_CODE_PREFIX . $code;
            $family['family'] = $code;

            foreach ($this->getLocalizedRandomLabels() as $localeCode => $label) {
                $family['label-' . $localeCode] = $label;
            }

            if (count($parentFamily['availableAxes']) >= 2 && 0 < $nbOfTwoLevelNeeded) {
                $level = $this->buildLevel($code, $maxAttributesPerAxe, true);
                $nbOfTwoLevelNeeded--;
            } elseif (0 < $nbOfOneLevelNeeded) {
                $level = $this->buildLevel($code, $maxAttributesPerAxe);
                $nbOfOneLevelNeeded--;
            }
            //Array merge
            //stop process if count is reach
            // Check if we have enough family variants

            $familyVariants[$family['code']] = $family;
            $progress->advance();
        }

        $this->familyVariants = $familyVariants;

        $this->writer
            ->setFilename(sprintf(
                '%s%s%s',
                $globalConfig['output_dir'],
                DIRECTORY_SEPARATOR,
                self::FAMILY_VARIANTS_FILENAME
            ))
            ->write($familyVariants);

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
     * @param string $familyParentCode
     * @param int    $maxAttributesPerAxe
     * @param bool   $twoLevels
     *
     * @return array
     */
    protected function buildLevel($familyParentCode, $maxAttributesPerAxe, $twoLevels = false)
    {
        $chunkSize = count($this->availableFamilies[$familyParentCode]['availableAttributes']) / 2;
        $availableAttributes = array_chunk(
            $this->availableFamilies[$familyParentCode]['availableAttributes'],
            $chunkSize
        );

        $axis1 = array_shift($this->availableFamilies[$familyParentCode]['availableAxes']);
        $levels['variant-axes_1'] = [$axis1->getCode()];
        $levels['variant-attributes_1'] = $this->buildAvailableAttributes(
            $availableAttributes[0],
            $maxAttributesPerAxe
        );

        if ($twoLevels) {
            $axis2 = array_shift($this->availableFamilies[$familyParentCode]['availableAxes']);
            $levels['variant-axes_2'] = [$axis2->getCode()];
            $levels['variant-attributes_2'] = $this->buildAvailableAttributes(
                $availableAttributes[1],
                $maxAttributesPerAxe
            );
        }

        return $levels;
    }

    /**
     * @param array $availableAttributes
     * @param int   $maxAttributes
     *
     * @return string
     */
    protected function buildAvailableAttributes(array $availableAttributes, $maxAttributes)
    {
        $variantAttributes = [];
        while (!empty($availableAttributes) || 0 !== $maxAttributes) {
            $attribute = array_shift($availableAttributes);
            $variantAttributes[] = $attribute->getCode();
            $maxAttributes--;
        }

        return implode(self::ATTRIBUTE_DELIMITER, $variantAttributes);
    }

    /**
     * Get localized random labels
     *
     * @return array
     */
    protected function getLocalizedRandomLabels()
    {
        $labels = [];

        foreach ($this->locales as $locale) {
            $labels[$locale->getCode()] = $this->faker->sentence(2);
        }

        return $labels;
    }

    /**
     * @param FamilyInterface[] $families
     * @param int               $count
     *
     * @throws \Exception if there is not enough families available to create asked number of family variants.
     */
    protected function setFamilies(array $families, $count)
    {
        foreach ($families as $family) {
            $attributes = $family->getAttributes();

            $availableAxes       = $this->getAvailableAxis($attributes);
            $availableAttributes = $this->getAvailableAttributes($attributes);

            if (0 !== count($availableAxes) && 0 !== count($availableAttributes)) {
                $this->availableFamilies[$family->getCode()] = [
                    'family'              => $family,
                    'availableAxes'       => $availableAxes,
                    'availableAttributes' => $availableAttributes
                ];
            }
        }

        if (count($this->availableFamilies) < $count) {
            throw new \Exception('There is not enough available families to create family variants.');
        }
    }

    /**
     * Returns the available attributes to define family variant axes
     * (only selects non localizable and non scopable).
     *
     * @param AttributeInterface[] $attributes
     *
     * @return AttributeInterface[]
     */
    protected function getAvailableAxis(array $attributes)
    {
        return array_filter($attributes, function ($attribute) {
            return in_array($attribute->getAttributeType(), [
                    AttributeTypes::OPTION_SIMPLE_SELECT,
                    AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT
                ]) && !$attribute->isLocalizable() && !$attribute->isScopable();
        });
    }

    /**
     * Returns the available attributes to define family variant attributes
     * (only texts non localizable and non scopable).
     *
     * @param AttributeInterface[] $attributes
     *
     * @return AttributeInterface[]
     */
    protected function getAvailableAttributes(array $attributes)
    {
        return array_filter($attributes, function ($attribute) {
            return (($attribute->getAttributeType() === AttributeTypes::TEXT)
                && !$attribute->isLocalizable()
                && !$attribute->isScopable()
            );
        });
    }

    /**
     * @param array $globalConfig
     */
    protected function setFaker(array $globalConfig)
    {
        $this->faker = Factory::create();
        if (isset($globalConfig['seed'])) {
            $this->faker->seed($globalConfig['seed']);
        }
    }

    /**
     * Returns the number of attributes to set.
     *
     * @param FamilyInterface $family
     * @param int             $nbAttrBase
     * @param int             $nbAttrDeviation
     *
     * @return int
     */
    private function getRandomAttributesCount(FamilyInterface $family, $nbAttrBase, $nbAttrDeviation)
    {
        if ($nbAttrBase > 0) {
            if ($nbAttrDeviation > 0) {
                $nbAttr = $this->faker->numberBetween(
                    $nbAttrBase - round($nbAttrDeviation / 2),
                    $nbAttrBase + round($nbAttrDeviation / 2)
                );
            } else {
                $nbAttr = $nbAttrBase;
            }
        }
        $familyAttrCount = count($this->getAttributesFromFamily($family));

        if (!isset($nbAttr) || $nbAttr > $familyAttrCount) {
            $nbAttr = $familyAttrCount;
        }

        return $nbAttr;
    }
}
