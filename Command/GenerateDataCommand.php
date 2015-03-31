<?php

namespace Pim\Bundle\DataGeneratorBundle\Command;

use Pim\Bundle\DataGeneratorBundle\Configuration\GeneratorConfiguration;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Processor;

/**
 * Generates CSV files for selected entities
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GenerateDataCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:generate-data')
            ->setDescription('Generate test data for PIM entities')
            ->addArgument(
                'configuration-file',
                InputArgument::REQUIRED,
                'YAML configuration file'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = $input->getArgument('configuration-file');

        $config = $this->getConfiguration($configFile);

        $generator = $this->getContainer()->get('pim_data_generator.generator');

        $totalCount = $this->getTotalCount($config);

        $outputDir = $config['output_dir'];

        $output->writeln(
            sprintf(
                '<info>Generating <comment>%d</comment> entities in the <comment>%s</comment> directory</info>',
                $totalCount,
                $outputDir
            )
        );

        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output, $totalCount);

        $generator->generate($config, $outputDir, $progress);
    }

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
        $config = new GeneratorConfiguration();

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
            $totalCount += $entity['count'];
        }

        return $totalCount;
    }
}
