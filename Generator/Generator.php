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
    /** @var ChannelGenerator */
    protected $channelGenerator;

    /** @var UserRoleGenerator */
    protected $userRoleGenerator;

    /** @var UserGroupGenerator */
    protected $userGroupGenerator;

    /** @var UserGenerator */
    protected $userGenerator;

    /** @var AttributeGenerator */
    protected $attributeGenerator;

    /** @var FamilyGenerator */
    protected $familyGenerator;

    /** @var ProductGenerator */
    protected $productGenerator;

    /** @var CategoryGenerator */
    protected $categoryGenerator;

    /** @var AttributeGroupGenerator */
    protected $attrGroupGenerator;

	/** @var AttributeOptionGenerator */
    protected $attributeOptionGenerator;

	/** @var JobGenerator */
    protected $jobGenerator;

    /** @var AssetCategoryGenerator */
    protected $assetCategoryGenerator;

    /** @var AssetCategoryAccessGenerator */
    protected $assetCategoryAccessGenerator;

    /** @var AttributeGroupsAccessGenerator */
    protected $attributeGroupsAccessGenerator;

    /**
     * @param ChannelGenerator               $channelGenerator
     * @param UserRoleGenerator              $userRoleGenerator
     * @param UserGroupGenerator             $userGroupGenerator
     * @param UserGenerator                  $userGenerator
     * @param AttributeGenerator             $attributeGenerator
     * @param FamilyGenerator                $familyGenerator
     * @param ProductGenerator               $productGenerator
     * @param CategoryGenerator              $categoryGenerator
     * @param AttributeGroupGenerator        $attrGroupGenerator
     * @param AttributeOptionGenerator       $attributeOptionGenerator
     * @param JobGenerator                   $jobGenerator
     * @param AssetCategoryGenerator         $assetCategoryGenerator
     * @param AssetCategoryAccessGenerator   $assetCategoryAccessGenerator
     * @param AttributeGroupsAccessGenerator $attributeGroupsAccessGenerator
     */
    public function __construct(
        ChannelGenerator               $channelGenerator,
        UserRoleGenerator              $userRoleGenerator,
        UserGroupGenerator             $userGroupGenerator,
        UserGenerator                  $userGenerator,
        AttributeGenerator             $attributeGenerator,
        FamilyGenerator                $familyGenerator,
        ProductGenerator               $productGenerator,
        CategoryGenerator              $categoryGenerator,
        AttributeGroupGenerator        $attrGroupGenerator,
        AttributeOptionGenerator       $attributeOptionGenerator,
        JobGenerator                   $jobGenerator,
        AssetCategoryGenerator         $assetCategoryGenerator,
        AssetCategoryAccessGenerator   $assetCategoryAccessGenerator,
        AttributeGroupsAccessGenerator $attributeGroupsAccessGenerator
    ) {
        $this->channelGenerator               = $channelGenerator;
        $this->userRoleGenerator              = $userRoleGenerator;
        $this->userGroupGenerator             = $userGroupGenerator;
        $this->userGenerator                  = $userGenerator;
        $this->attributeGenerator             = $attributeGenerator;
        $this->familyGenerator                = $familyGenerator;
        $this->productGenerator               = $productGenerator;
        $this->categoryGenerator              = $categoryGenerator;
        $this->attrGroupGenerator             = $attrGroupGenerator;
        $this->attributeOptionGenerator       = $attributeOptionGenerator;
        $this->jobGenerator                   = $jobGenerator;
        $this->assetCategoryGenerator         = $assetCategoryGenerator;
        $this->assetCategoryAccessGenerator   = $assetCategoryAccessGenerator;
        $this->attributeGroupsAccessGenerator = $attributeGroupsAccessGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress, array $options = null)
    {
        $locales    = [];
        $currencies = [];
        $channels   = [];

        $userRoles  = [];
        $userGroups = [];
        $users      = [];
        $categories = [];

        $attributeGroups = [];
        $attributes      = [];

        $assetCategoryCodes = [];

        if (isset($config['entities']['product']) && count($config['entities']) > 1) {
            throw new \LogicException(
                'Products can be generated at the same time of other entities.'.
                'Please generate attributes and families, import them, then generate products'
            );
        }

        if (isset($config['entities']['channels'])) {
            $channelConfig = $config['entities']['channels'];
            $this->channelGenerator->generate($channelConfig, $outputDir, $progress);
            $locales    = $this->channelGenerator->getLocales();
            $currencies = $this->channelGenerator->getCurrencies();
            $channels   = $this->channelGenerator->getChannels();
        }

        if (isset($config['entities']['categories'])) {
            $categoryConfig = $config['entities']['categories'];
            $this->categoryGenerator->setLocales($locales);
            $categories = $this->categoryGenerator->generate($categoryConfig, $outputDir, $progress);
        }

        if (isset($config['entities']['user_roles'])) {
            $userRoleConfig = $config['entities']['user_roles'];
            $userRoles = $this->userRoleGenerator->generate($userRoleConfig, $outputDir);
        }

        if (isset($config['entities']['user_groups'])) {
            $userGroupConfig = $config['entities']['user_groups'];
            $userGroups = $this->userGroupGenerator->generate($userGroupConfig, $outputDir);
        }

        if (isset($config['entities']['asset_categories'])) {
            $this->assetCategoryGenerator->setLocales($locales);
            $assetCategoryCodes = $this->assetCategoryGenerator->generate([], $outputDir, $progress);
        }

        if (isset($config['entities']['users'])) {
            $userConfig = $config['entities']['users'];
            $this->userGenerator->setLocales($locales);
            $this->userGenerator->setChannels($channels);
            $this->userGenerator->setCategories($categories);
            $this->userGenerator->setUserRoles($userRoles);
            $this->userGenerator->setUserGroups($userGroups);
            $this->userGenerator->setAssetCategories($assetCategoryCodes);
            $users = $this->userGenerator->generate($userConfig, $outputDir);
        }

        if (isset($config['entities']['attribute_groups'])) {
            $attributeGroupConfig = $config['entities']['attribute_groups'];
            $this->attrGroupGenerator->setLocales($locales);
            $this->attrGroupGenerator->generate($attributeGroupConfig, $outputDir, $progress);
            $attributeGroups = $this->attrGroupGenerator->getAttributeGroups();
        }

        if (isset($config['entities']['attributes'])) {
            $attributeConfig = $config['entities']['attributes'];
            $this->attributeGenerator->setAttributeGroups($attributeGroups);
            $this->attributeGenerator->setLocales($locales);
            $this->attributeGenerator->generate($attributeConfig, $outputDir, $progress);
            $attributes = $this->attributeGenerator->getAttributes();
        }

        if (isset($config['entities']['families'])) {
            $familyConfig = $config['entities']['families'];
            $this->familyGenerator->setChannels($channels);
            $this->familyGenerator->setLocales($locales);
            $this->familyGenerator->setAttributes($attributes);
            $this->familyGenerator->generate($familyConfig, $outputDir, $progress);
        }

        if (isset($config['entities']['jobs'])) {
            $jobConfig = $config['entities']['jobs'];
            $this->jobGenerator->generate($jobConfig, $outputDir);
        }

        if (isset($config['entities']['attribute_options'])) {
            $attributeOptionConfig = $config['entities']['attribute_options'];
            $this->attributeOptionGenerator->setLocales($locales);
            $this->attributeOptionGenerator->setAttributes($attributes);
            $this->attributeOptionGenerator->generate($attributeOptionConfig, $outputDir, $progress);
        }

        if (isset($config['entities']['products'])) {
            $productConfig = $config['entities']['products'];
            $this->productGenerator->generate($productConfig, $outputDir, $progress);
        }

        if (isset($config['entities']['asset_category_accesses'])) {
            $this->assetCategoryAccessGenerator->setGroups($userGroups);
            $this->assetCategoryAccessGenerator->setAssetCategories($assetCategoryCodes);
            $this->assetCategoryAccessGenerator->generate([], $outputDir, $progress);
        }

        if (isset($config['entities']['attribute_groups_accesses'])) {
            $this->attributeGroupsAccessGenerator->setGroups($userGroups);
            $this->attributeGroupsAccessGenerator->setAttributeGroups($attributeGroups);
            $this->attributeGroupsAccessGenerator->generate([], $outputDir, $progress);
        }
    }
}
