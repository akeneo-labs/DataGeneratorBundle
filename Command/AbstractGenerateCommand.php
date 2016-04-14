<?php

namespace Pim\Bundle\DataGeneratorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Generates CSV products file
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractGenerateCommand extends ContainerAwareCommand
{
    /**
     * Return a processed configuration from the configuration filename provided
     *
     * @param string                 $filename
     * @param ConfigurationInterface $config
     *
     * @return array
     */
    protected function getConfiguration($filename, $config)
    {
        $rawConfig = Yaml::parse(file_get_contents($filename));

        $processor = new Processor();

        $processedConfig = $processor->processConfiguration(
            $config,
            $rawConfig
        );

        return $processedConfig;
    }

    /**
     * Get total count from the configuration
     *
     * @param array $config
     *
     * @return int
     */
    protected function getTotalCount(array $config)
    {
        $totalCount = 0;

        foreach ($config['entities'] as $entity) {
            if (isset($entity['count'])) {
                $totalCount += $entity['count'];
            } else {
                $totalCount += 1;
            }
        }

        return $totalCount;
    }

    /**
    * Checks if the output directory exists
    *
    * @param string $outputDir
    */
    protected function checkOutputDirExists($outputDir)
    {
        $fs = new Filesystem();
        if (!$fs->exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }
    }
}
