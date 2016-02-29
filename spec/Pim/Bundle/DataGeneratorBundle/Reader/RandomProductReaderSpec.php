<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Reader;

use PhpSpec\ObjectBehavior;

class RandomProductReader extends ObjectBehavior
{
    function it_is_a_reader()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemReaderInterface');
    }

    function it_reads_a_product()
    {
        $this->read()->shouldImplement('Pim\Component\Catalog\Model\ProductInterface');
    }
}
