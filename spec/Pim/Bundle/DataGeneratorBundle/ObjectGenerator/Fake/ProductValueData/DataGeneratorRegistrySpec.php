<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\ObjectGenerator\Fake\ProductValueData;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Bundle\DataGeneratorBundle\ObjectGenerator\Fake\ProductValueData\DataGeneratorInterface;

class DataGeneratorRegistrySpec extends ObjectBehavior
{
    public function it_registers_and_provide_data_generator(
        DataGeneratorInterface $generator,
        AttributeInterface $attribute
    ) {
        $generator->supportsGeneration($attribute)->willReturn(true);

        $this->register($generator);

        $this->getDataGenerator($attribute)->shouldReturn($generator);
    }

    public function it_provides_the_right_data_generator(
        DataGeneratorInterface $varcharGen,
        DataGeneratorInterface $decimalGen,
        AttributeInterface $attrBoolean,
        AttributeInterface $attrVarchar,
        AttributeInterface $attrDecimal
    ) {
        $varcharGen->supportsGeneration($attrVarchar)->willReturn(true);
        $varcharGen->supportsGeneration($attrBoolean)->willReturn(false);
        $varcharGen->supportsGeneration($attrDecimal)->willReturn(false);

        $decimalGen->supportsGeneration($attrVarchar)->willReturn(false);
        $decimalGen->supportsGeneration($attrBoolean)->willReturn(false);
        $decimalGen->supportsGeneration($attrDecimal)->willReturn(true);

        $this->register($varcharGen);
        $this->register($decimalGen);

        $this->getDataGenerator($attrBoolean)->shouldNotReturn($varcharGen);
        $this->getDataGenerator($attrBoolean)->shouldNotReturn($decimalGen);

        $this->getDataGenerator($attrVarchar)->shouldReturn($varcharGen);
        $this->getDataGenerator($attrVarchar)->shouldNotReturn($decimalGen);

        $this->getDataGenerator($attrDecimal)->shouldNotReturn($varcharGen);
        $this->getDataGenerator($attrDecimal)->shouldReturn($decimalGen);
    }
}

