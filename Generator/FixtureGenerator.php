<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Symfony\Component\Console\Helper\ProgressHelper;

/**
 * Fixture generator that will dispatch generation to specialized generator
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FixtureGenerator implements GeneratorInterface
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

    /** @var JobProfilesAccessGenerator */
    protected $jobProfilesAccessGenerator;

    /** @var LocalesAccessGenerator */
    protected $localesAccessGenerator;

    /** @var ProductCategoryAccessGenerator */
    protected $productCategoryAccessGenerator;

    /** @var AssociationTypeGenerator */
    protected $associationTypeGenerator;

    /** @var GroupTypeGenerator */
    protected $groupTypeGenerator;

    /** @var VariantGroupGenerator */
    protected $variantGroupGenerator;

    /**
     * @param ChannelGenerator               $channelGenerator
     * @param UserRoleGenerator              $userRoleGenerator
     * @param UserGroupGenerator             $userGroupGenerator
     * @param UserGenerator                  $userGenerator
     * @param AttributeGenerator             $attributeGenerator
     * @param FamilyGenerator                $familyGenerator
     * @param CategoryGenerator              $categoryGenerator
     * @param AttributeGroupGenerator        $attrGroupGenerator
     * @param AttributeOptionGenerator       $attributeOptionGenerator
     * @param JobGenerator                   $jobGenerator
     * @param AssetCategoryGenerator         $assetCategoryGenerator
     * @param AssetCategoryAccessGenerator   $assetCategoryAccessGenerator
     * @param AttributeGroupsAccessGenerator $attributeGroupsAccessGenerator
     * @param JobProfilesAccessGenerator     $jobProfilesAccessGenerator
     * @param LocalesAccessGenerator         $localesAccessGenerator
     * @param ProductCategoryAccessGenerator $productCategoryAccessGenerator
     * @param AssociationTypeGenerator       $associationTypeGenerator
     * @param GroupTypeGenerator             $groupTypeGenerator
     * @param VariantGroupGenerator          $variantGroupGenerator
     */
    public function __construct(
        ChannelGenerator               $channelGenerator,
        UserRoleGenerator              $userRoleGenerator,
        UserGroupGenerator             $userGroupGenerator,
        UserGenerator                  $userGenerator,
        AttributeGenerator             $attributeGenerator,
        FamilyGenerator                $familyGenerator,
        CategoryGenerator              $categoryGenerator,
        AttributeGroupGenerator        $attrGroupGenerator,
        AttributeOptionGenerator       $attributeOptionGenerator,
        JobGenerator                   $jobGenerator,
        AssetCategoryGenerator         $assetCategoryGenerator,
        AssetCategoryAccessGenerator   $assetCategoryAccessGenerator,
        AttributeGroupsAccessGenerator $attributeGroupsAccessGenerator,
        JobProfilesAccessGenerator     $jobProfilesAccessGenerator,
        LocalesAccessGenerator         $localesAccessGenerator,
        ProductCategoryAccessGenerator $productCategoryAccessGenerator,
        AssociationTypeGenerator       $associationTypeGenerator,
        GroupTypeGenerator             $groupTypeGenerator,
        VariantGroupGenerator          $variantGroupGenerator
    ) {
        $this->channelGenerator               = $channelGenerator;
        $this->userRoleGenerator              = $userRoleGenerator;
        $this->userGroupGenerator             = $userGroupGenerator;
        $this->userGenerator                  = $userGenerator;
        $this->attributeGenerator             = $attributeGenerator;
        $this->familyGenerator                = $familyGenerator;
        $this->categoryGenerator              = $categoryGenerator;
        $this->attrGroupGenerator             = $attrGroupGenerator;
        $this->attributeOptionGenerator       = $attributeOptionGenerator;
        $this->jobGenerator                   = $jobGenerator;
        $this->assetCategoryGenerator         = $assetCategoryGenerator;
        $this->assetCategoryAccessGenerator   = $assetCategoryAccessGenerator;
        $this->attributeGroupsAccessGenerator = $attributeGroupsAccessGenerator;
        $this->jobProfilesAccessGenerator     = $jobProfilesAccessGenerator;
        $this->localesAccessGenerator         = $localesAccessGenerator;
        $this->productCategoryAccessGenerator = $productCategoryAccessGenerator;
        $this->associationTypeGenerator       = $associationTypeGenerator;
        $this->groupTypeGenerator             = $groupTypeGenerator;
        $this->variantGroupGenerator          = $variantGroupGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress, array $options = null)
    {
        $locales            = [];
        $channels           = [];
        $userRoles          = [];
        $userGroups         = [];
        $categories         = [];
        $attributeGroups    = [];
        $attributes         = [];
        $assetCategoryCodes = [];
        $jobs               = [];

        if (isset($config['entities']['channels'])) {
            $channelConfig = $config['entities']['channels'];
            $this->channelGenerator->generate($channelConfig, $outputDir, $progress);
            $locales    = $this->channelGenerator->getLocales();
            $channels   = $this->channelGenerator->getChannels();
        }

        if (isset($config['entities']['associations'])) {
            $associationConfig = $config['entities']['associations'];
            $this->associationTypeGenerator->generate($associationConfig, $outputDir, $progress, [
                'locales' => $locales,
            ]);
        }

        if (isset($config['entities']['categories'])) {
            $categoryConfig = $config['entities']['categories'];
            $categories = $this->categoryGenerator->generate($categoryConfig, $outputDir, $progress, [
                'locales' => $locales,
            ]);
        }

        if (isset($config['entities']['user_roles'])) {
            $userRoleConfig = $config['entities']['user_roles'];
            $userRoles = $this->userRoleGenerator->generate($userRoleConfig, $outputDir, $progress);
        }

        if (isset($config['entities']['user_groups'])) {
            $userGroupConfig = $config['entities']['user_groups'];
            $userGroups = $this->userGroupGenerator->generate($userGroupConfig, $outputDir, $progress);
        }

        if (isset($config['entities']['asset_categories'])) {
            $assetCategoryCodes = $this->assetCategoryGenerator->generate([], $outputDir, $progress, [
                'locales' => $locales,
            ]);
        }

        if (isset($config['entities']['users'])) {
            $userConfig = $config['entities']['users'];
            $this->userGenerator->generate($userConfig, $outputDir, $progress, [
                'locales'              => $locales,
                'channels'             => $channels,
                'categories'           => $categories,
                'user_roles'           => $userRoles,
                'user_groups'          => $userGroups,
                'asset_category_codes' => $assetCategoryCodes,
            ]);
        }

        if (isset($config['entities']['attribute_groups'])) {
            $attributeGroupConfig = $config['entities']['attribute_groups'];
            $this->attrGroupGenerator->generate($attributeGroupConfig, $outputDir, $progress, [
                'locales' => $locales,
            ]);
            $attributeGroups = $this->attrGroupGenerator->getAttributeGroups();
        }

        if (isset($config['entities']['attributes'])) {
            $attributeConfig = $config['entities']['attributes'];
            if (isset($config['entities']['variant_groups']['axes_count'])) {
                $variantGroupAxisCount = $config['entities']['variant_groups']['axes_count'];
                $attributeConfig['min_variant_axes'] = $variantGroupAxisCount;
            }
            if (isset($config['entities']['variant_groups']['attributes_count'])) {
                $variantGroupAttributesCount = $config['entities']['variant_groups']['attributes_count'];
                $attributeConfig['min_variant_attributes'] = $variantGroupAttributesCount;
            }
            $this->attributeGenerator->generate($attributeConfig, $outputDir, $progress, [
                'locales'          => $locales,
                'attribute_groups' => $attributeGroups
            ]);
            $attributes = $this->attributeGenerator->getAttributes();
        }

        if (isset($config['entities']['families'])) {
            $familyConfig = $config['entities']['families'];
            $this->familyGenerator->generate($familyConfig, $outputDir, $progress, [
                'channels'   => $channels,
                'locales'    => $locales,
                'attributes' => $attributes,
            ]);
        }

        if (isset($config['entities']['jobs'])) {
            $jobConfig = $config['entities']['jobs'];
            $jobs = $this->jobGenerator->generate($jobConfig, $outputDir, $progress);
        }

        if (isset($config['entities']['attribute_options'])) {
            $attributeOptionConfig = $config['entities']['attribute_options'];
            $this->attributeOptionGenerator->generate($attributeOptionConfig, $outputDir, $progress, [
                'locales'    => $locales,
                'attributes' => $attributes,
            ]);
        }

        if (isset($config['entities']['group_types'])) {
            $groupTypes = $this->groupTypeGenerator->generate([], $outputDir, $progress);
        }

        if (isset($config['entities']['variant_groups'])) {
            $variantGroupConfig = $config['entities']['variant_groups'];
            $this->variantGroupGenerator->generate($variantGroupConfig, $outputDir, $progress, [
                'attributes'  => $attributes,
                'locales'     => $locales,
                'group_types' => $groupTypes,
            ]);
        }

        if (isset($config['entities']['asset_category_accesses'])) {
            $this->assetCategoryAccessGenerator->generate([], $outputDir, $progress, [
                'groups'              => $userGroups,
                'asset_category_code' => $assetCategoryCodes,
            ]);
        }

        if (isset($config['entities']['attribute_groups_accesses'])) {
            $this->attributeGroupsAccessGenerator->generate([], $outputDir, $progress, [
                'groups'           => $userGroups,
                'attribute_groups' => $attributeGroups,
            ]);
        }

        if (isset($config['entities']['job_profiles_accesses'])) {
            $this->jobProfilesAccessGenerator->generate([], $outputDir, $progress, [
                'groups' => $userGroups,
                'jobs'   => $jobs,
            ]);
        }

        if (isset($config['entities']['locales_accesses'])) {
            $this->localesAccessGenerator->generate([], $outputDir, $progress, [
                'groups'  => $userGroups,
                'locales' => $locales,
            ]);
        }

        if (isset($config['entities']['product_category_accesses'])) {
            $this->productCategoryAccessGenerator->generate([], $outputDir, $progress, [
                'groups'     => $userGroups,
                'categories' => $categories,
            ]);
        }
    }
}
