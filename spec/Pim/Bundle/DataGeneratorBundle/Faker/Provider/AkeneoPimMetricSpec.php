<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Faker\Provider;

use Faker\Generator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AkeneoPimMetricSpec extends ObjectBehavior
{
    function let(Generator $generator)
    {
        $this->beConstructedWith($generator);
    }

    function it_generates_metric()
    {
        $this->akeneoPimMetric('length', 'meter')->shouldReturnAnInstanceOf('Pim\Bundle\CatalogBundle\Model\Metric');
    }

    function it_generates_metric_with_family_and_unit()
    {
        $metric = $this->akeneoPimMetric('length', 'meter');
        $metric->getFamily()->shouldReturn('length');
        $metric->getUnit()->shouldReturn('meter');
    }
}
