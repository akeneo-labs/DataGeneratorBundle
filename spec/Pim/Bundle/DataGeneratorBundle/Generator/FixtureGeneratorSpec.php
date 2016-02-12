<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Generator\AssetCategoryAccessGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\AssetCategoryGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\AssociationTypeGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\AttributeGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\AttributeGroupGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\AttributeGroupsAccessGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\AttributeOptionGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\CategoryGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\ChannelGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\FamilyGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\GroupTypeGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\JobGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\JobProfilesAccessGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\LocalesAccessGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\ProductCategoryAccessGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\UserGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\UserGroupGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\UserRoleGenerator;
use Pim\Bundle\DataGeneratorBundle\Generator\VariantGroupGenerator;
use Prophecy\Argument;

class FixtureGeneratorSpec extends ObjectBehavior
{
    function let(
        ChannelGenerator $channelGenerator,
        UserRoleGenerator $userRoleGenerator,
        UserGroupGenerator $userGroupGenerator,
        UserGenerator $userGenerator,
        AttributeGenerator $attributeGenerator,
        FamilyGenerator $familyGenerator,
        CategoryGenerator $categoryGenerator,
        AttributeGroupGenerator $attrGroupGenerator,
        AttributeOptionGenerator $attributeOptionGenerator,
        JobGenerator $jobGenerator,
        AssetCategoryGenerator $assetCategoryGenerator,
        AssetCategoryAccessGenerator $assetCategoryAccessGenerator,
        AttributeGroupsAccessGenerator $attributeGroupsAccessGenerator,
        JobProfilesAccessGenerator $jobProfilesAccessGenerator,
        LocalesAccessGenerator $localesAccessGenerator,
        ProductCategoryAccessGenerator $productCategoryAccessGenerator,
        AssociationTypeGenerator $associationTypeGenerator,
        GroupTypeGenerator $groupTypeGenerator,
        VariantGroupGenerator $variantGroupGenerator
    ) {
        $this->beConstructedWith(
            $channelGenerator,
            $userRoleGenerator,
            $userGroupGenerator,
            $userGenerator,
            $attributeGenerator,
            $familyGenerator,
            $categoryGenerator,
            $attrGroupGenerator,
            $attributeOptionGenerator,
            $jobGenerator,
            $assetCategoryGenerator,
            $assetCategoryAccessGenerator,
            $attributeGroupsAccessGenerator,
            $jobProfilesAccessGenerator,
            $localesAccessGenerator,
            $productCategoryAccessGenerator,
            $associationTypeGenerator,
            $groupTypeGenerator,
            $variantGroupGenerator
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\FixtureGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldImplement('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }
}
