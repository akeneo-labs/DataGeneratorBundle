<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Symfony\Component\Console\Helper\ProgressHelper;

/**
 * Generic generator that will dispatch generation to specialized generator
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Generator implements GeneratorInterface
{
    /** @var CurrencyGenerator */
    protected $currencyGenerator;

    /** @var ChannelGenerator */
    protected $channelGenerator;

    /** @var AttributeGenerator */
    protected $attributeGenerator;

    /** @var FamilyGenerator */
    protected $familyGenerator;

    /** @var ProductGenerator */
    protected $productGenerator;

    /**
     * @param CurrencyGenerator  $currencyGenerator
     * @param ChannelGenerator   $channelGenerator
     * @param AttributeGenerator $attributeGenerator
     * @param FamilyGenerator    $familyGenerator
     * @param ProductGenerator   $productGenerator
     */
    public function __construct(
        CurrencyGenerator $currencyGenerator,
        ChannelGenerator $channelGenerator,
        AttributeGenerator $attributeGenerator,
        FamilyGenerator $familyGenerator,
        ProductGenerator $productGenerator
    ) {
        $this->currencyGenerator  = $currencyGenerator;
        $this->channelGenerator   = $channelGenerator;
        $this->attributeGenerator = $attributeGenerator;
        $this->familyGenerator    = $familyGenerator;
        $this->productGenerator   = $productGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress)
    {
        $generatedAttributes = null;
        $generatedCurrencies = null;

        if (!is_writable($outputDir)) {
            throw new \LogicException(
                sprintf(
                    "The directory %s is not writable.",
                    $outputDir
                )
            );
        }

        if (isset($config['entities']['product']) && count($config['entities']) > 1) {
            throw new \LogicException(
                'Products can be generated at the same time of other entities.'.
                'Please generate attributes and families, import them, then generate products'
            );
        }

        if (isset($config['entities']['currencies'])) {
            $currenciesConfig = $config['entities']['currencies'];
            $generatedCurrencies = $this->currencyGenerator->generate($currenciesConfig, $outputDir, $progress);
        }

        if (isset($config['entities']['channels'])) {
            $channelsConfig = $config['entities']['channels'];
            if (null !== $generatedCurrencies) {
                $this->channelGenerator->setCurrencies($generatedCurrencies);
            }
            $generatedChannels = $this->channelGenerator->generate($channelsConfig, $outputDir, $progress);
        }

        if (isset($config['entities']['attributes'])) {
            $attributesConfig = $config['entities']['attributes'];
            $generatedAttributes = $this->attributeGenerator->generate($attributeConfig, $outputDir, $progress);
        }

        if (isset($config['entities']['families'])) {
            $familiesConfig = $config['entities']['families'];
            if (null !== $generatedAttributes) {
                $this->familyGenerator->setAttributes($generatedAttributes);
            }
            $generatedFamilies = $this->familyGenerator->generate($familiesConfig, $outputDir, $progress);
        }

        if (isset($config['entities']['products'])) {
            $productsConfig = $config['entities']['products'];
            if (null !== $generatedAttributes) {
                $this->productGenerator->setExtraAttributes($generatedAttributes);
            }
            $this->productGenerator->generate($productsConfig, $outputDir, $progress);
        }

        $progress->finish();
    }
}
