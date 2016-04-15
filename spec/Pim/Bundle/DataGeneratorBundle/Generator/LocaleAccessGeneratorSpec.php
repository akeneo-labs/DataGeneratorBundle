<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Component\Catalog\Model\LocaleInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;

class LocaleAccessGeneratorSpec extends ObjectBehavior
{
    function let(CsvWriter $writer)
    {
        $this->beConstructedWith($writer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\LocaleAccessGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_supports_locale_accesses()
    {
        $this->supports('locale_accesses')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }

    function it_generates_locale_accesses(
        $writer,
        Group $userGroup1,
        Group $userGroup2,
        Group $userGroupAll,
        LocaleInterface $locale1,
        LocaleInterface $locale2,
        ProgressBar $progress
    ) {
        $userGroup1->getName()->willReturn('UserGroup1');
        $userGroup2->getName()->willReturn('UserGroup2');
        $userGroupAll->getName()->willReturn('All');
        $locale1->getCode()->willReturn('fr_FR');
        $locale2->getCode()->willReturn('en_US');

        $globalConfig   = ['output_dir' => '/'];
        $entitiesConfig = [];
        $options        = [
            'user_groups' => [$userGroup1, $userGroup2],
            'locales'     => [$locale1, $locale2],
        ];

        $writer->setFilename(Argument::any())->willReturn($writer);
        $writer->write(
            [
                [
                    'locale'   => 'fr_FR',
                    'view_products' => 'UserGroup1,UserGroup2',
                    'edit_products' => 'UserGroup1,UserGroup2'
                ], [
                    'locale'   => 'en_US',
                    'view_products' => 'UserGroup1,UserGroup2',
                    'edit_products' => 'UserGroup1,UserGroup2'
                ]
            ]
        )->shouldBeCalled();

        $this->generate($globalConfig, $entitiesConfig, $progress, $options);
    }
}
