<?php

namespace spec\Pim\Bundle\DataGeneratorBundle\Writer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;


class CsvWriterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter');
    }

    function it_is_a_writer()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemWriterInterface');
    }

    function it_has_a_filename()
    {
        $this->setFileName('filename.csv')->shouldReturn($this);
    }

    function it_writes_a_csv_filename()
    {
        $file = '/tmp/filename.csv';
        $this->setFileName($file);

        $this->write([
            [
                'column' => 'value',
                'other_column' => 'other_value',
            ]
        ]);

        assert(is_file($file), 'Impossible to generate the csv file');
    }
}
