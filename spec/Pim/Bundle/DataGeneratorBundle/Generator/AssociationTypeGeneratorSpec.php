<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Component\Catalog\Model\LocaleInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;

class AssociationTypeGeneratorSpec extends ObjectBehavior
{
    function let(CsvWriter $writer)
    {
        $this->beConstructedWith($writer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\AssociationTypeGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_supports_association_types()
    {
        $this->supports('association_types')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }

    function it_generates_association_types(
        $writer,
        ProgressBar $progress,
        LocaleInterface $locale1,
        LocaleInterface $locale2
    ) {
        $globalConfig = ['output_dir' => '/', 'seed' => 123456789];
        $entitiesConfig = ['count' => 2];
        $options = ['locales' => [$locale1, $locale2]];
        $locale1->getCode()->willReturn('fr_FR');
        $locale2->getCode()->willReturn('en_US');

        $writer->setFilename(Argument::any())->willReturn($writer);

        $writer->write([
            [
                'code'        => 'LAUDANTIUM',
                'label-fr_FR' => 'aut',
                'label-en_US' => 'sed'
            ], [
                'code'        => 'DOLORIBUS',
                'label-fr_FR' => 'molestias',
                'label-en_US' => 'minima'
            ]
        ])->shouldBeCalled();

        $this->generate($globalConfig, $entitiesConfig, $progress, $options);
    }
}
