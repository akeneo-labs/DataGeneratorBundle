<?php

namespace Pim\Bundle\DataGeneratorBundle\Writer;

/**
 * Write CSV files
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvWriter implements WriterInterface
{
    /** @var string */
    protected $outputFile;

    /** @var array */
    protected $data;

    /**
     * {@inheritdoc}
     */
    public function setFilename($filename)
    {
        $this->outputFile = $filename;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Write the CSV file from products and headers
     */
    public function write()
    {
        if (0 === count($this->data)) {
            return;
        }

        $csvFile = fopen($this->outputFile, 'w');

        $headers = $this->getHeaders();
        fputcsv($csvFile, $headers, ';');

        $headersAsKeys = array_fill_keys($headers, '');

        foreach ($this->data as $item) {
            $filledItem = array_merge($headersAsKeys, $item);
            fputcsv($csvFile, $filledItem, ';');
        }
        fclose($csvFile);
    }

    /**
     * Return the headers for CSV generation.
     * @return array
     */
    protected function getHeaders()
    {
        $headers = [];
        foreach ($this->data as $item) {
            foreach ($item as $key => $value) {
                if (!in_array($key, $headers)) {
                    $headers[] = $key;
                }
            }
        }

        return $headers;
    }
}
