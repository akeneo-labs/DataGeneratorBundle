<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

class CsvWriter
{
    /** @var string */
    protected $outputFile;

    /** @var array */
    protected $data;

    public function __construct($outputFile, $data)
    {
        $this->outputFile = $outputFile;
        $this->data       = $data;
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
