<?php

namespace Pim\Bundle\DataGeneratorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
     * @param string $filename
     *
     * @return array
     */
    protected function getConfiguration($filename)
    {
        $rawConfig = Yaml::parse(file_get_contents($filename));
        $processor = new Processor();
        $processedConfig = $processor->processConfiguration(
            $this->getGeneratorConfiguration(),
            $rawConfig
        );

        return $processedConfig;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = $input->getArgument('configuration-file');

        $config = $this->getConfiguration($configFile);

        $generator = $this->getContainer()->get('pim_data_generator.chained_generator');

        $totalCount = $this->getTotalCount($config);

        $outputDir = $config['output_dir'];
        $this->generateOutputDir($outputDir);

        $output->writeln(
            sprintf(
                '<info>Generating <comment>%d</comment> entities in the <comment>%s</comment> directory</info>',
                $totalCount,
                $outputDir
            )
        );

        $progress = new ProgressBar($output, $totalCount);
        $progress->setFormat(' %current%/%max% [%bar%] %elapsed:6s%/%estimated:-6s% %memory:6s% - %message%');
        $progress->start();
        $generator->generate($config, $progress);
        $progress->finish();
        $output->writeln('');
        $output->writeln('<info>Entities generated!</info>');
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
    * Checks if the output directory exists, creates it if not.
    *
    * @param string $outputDir
    */
    protected function generateOutputDir($outputDir)
    {
        $fs = new Filesystem();
        if (!$fs->exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }
    }

    /**
     * @return ConfigurationInterface
     */
    abstract protected function getGeneratorConfiguration();
}
