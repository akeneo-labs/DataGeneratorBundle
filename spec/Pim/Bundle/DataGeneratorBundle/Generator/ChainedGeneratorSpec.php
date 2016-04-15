<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface;
use Pim\Bundle\DataGeneratorBundle\Generator\GeneratorRegistry;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;

class ChainedGeneratorSpec extends ObjectBehavior
{
    function let(GeneratorRegistry $registry)
    {
        $this->beConstructedWith($registry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\ChainedGenerator');
    }

    function it_should_generate_entities(
        $registry,
        ProgressBar $progress,
        GeneratorInterface $generator1,
        GeneratorInterface $generator2
    ) {
        $globalConfig = [
            'output_dir' => '/',
            'entities' => [
                'entity1' => ['configEntity1' => 'valueEntity1'],
                'entity2' => ['configEntity2' => 'valueEntity2'],
            ],
        ];

        $registry->getGenerator('entity1')->willReturn($generator1);
        $registry->getGenerator('entity2')->willReturn($generator2);

        $generator1
            ->generate(['output_dir' => '/'], ['configEntity1' => 'valueEntity1'], $progress, [])
            ->shouldBeCalled()
            ->willReturn(['entity1' => ['value1']]);
        $generator2
            ->generate(['output_dir' => '/'], ['configEntity2' => 'valueEntity2'], $progress, ['entity1' => ['value1']])
            ->shouldBeCalled()
            ->willReturn([]);

        $this->generate($globalConfig, $progress);
    }
}
