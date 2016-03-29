<?php

namespace Pim\Bundle\DataGeneratorBundle\Writer;

use Akeneo\Component\Batch\Item\ItemWriterInterface;

/**
 * Write CSV files
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvWriter implements ItemWriterInterface
{
    /** @var string */
    protected $outputFile;

    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return CsvWriter
     */
    public function setFilename($filename)
    {
        $this->outputFile = $filename;

        return $this;
    }

    /**
     * Write the CSV file from products and headers
     *
     * @param array $data
     */
    public function write(array $data)
    {
        if (0 === count($data)) {
            return;
        }

        $csvFile = fopen($this->outputFile, 'w');

        $headers = $this->getHeaders($data);
        fputcsv($csvFile, $headers, ';');

        $headersAsKeys = array_fill_keys($headers, '');

        foreach ($data as $item) {
            $filledItem = array_merge($headersAsKeys, $item);
            fputcsv($csvFile, $filledItem, ';');
        }
        fclose($csvFile);
    }

    /**
     * Return the headers for CSV generation.
     *
     * @param array $data
     *
     * @return array
     */
    protected function getHeaders(array $data)
    {
        $headers = [];
        foreach ($data as $item) {
            foreach ($item as $key => $value) {
                if (!in_array($key, $headers)) {
                    $headers[] = $key;
                }
            }
        }

        return $headers;
    }
}
