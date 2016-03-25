<?php

namespace Pim\Bundle\DataGeneratorBundle\Writer;

/**
 * Defines an interface for writers.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface WriterInterface
{
    /**
     * Set the filename to write.
     *
     * @param string $filename
     *
     * @return WriterInterface
     */
    public function setFilename($filename);

    /**
     * Set the data to write
     *
     * @param mixed $data
     *
     * @return WriterInterface
     */
    public function setData($data);

    /**
     * Write the data
     */
    public function write();
}
