<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Generator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;

class UserRoleGeneratorSpec extends ObjectBehavior
{
    function let(CsvWriter $writer)
    {
        $this->beConstructedWith($writer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\UserRoleGenerator');
    }

    function it_is_a_generator()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Generator\GeneratorInterface');
    }

    function it_supports_user_roles()
    {
        $this->supports('user_roles')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }

    function it_generates_user_roles(
        $writer,
        ProgressBar $progress
    ) {
        $globalConfig = ['output_dir' => '/'];
        $entitiesConfig = [
            'ROLE_ADMINISTRATOR' => ['label' => 'Administrator'],
            'ROLE_REDACTOR'      => ['label' => 'Redactor'],
        ];
        $options = [];

        $writer->setFilename(Argument::any())->willReturn($writer);

        $writer->write([
            ['label' => 'Administrator', 'role' => 'ROLE_ADMINISTRATOR'],
            ['label' => 'Redactor', 'role' => 'ROLE_REDACTOR']
        ])->shouldBeCalled();

        $this->generate($globalConfig, $entitiesConfig, $progress, $options);
    }
}
