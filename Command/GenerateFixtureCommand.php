<?php

namespace Pim\Bundle\DataGeneratorBundle\Command;

use Pim\Bundle\DataGeneratorBundle\Configuration\FixtureGeneratorConfiguration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates CSV files for selected entities
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GenerateFixtureCommand extends AbstractGenerateCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:generate:fixtures')
            ->setDescription('Generate test fixtures for PIM entities')
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

        $config = $this->getConfiguration($configFile, new FixtureGeneratorConfiguration());

        $generator = $this->getContainer()->get('pim_data_generator.fixture_generator');

        $totalCount = $this->getTotalCount($config);

        $outputDir = $config['output_dir'];
        $this->checkOutputDirExists($outputDir);

        $output->writeln(
            sprintf(
                '<info>Generating <comment>%d</comment> entities in the <comment>%s</comment> directory</info>',
                $totalCount,
                $outputDir
            )
        );

        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output, $totalCount);

        $generator->generate($config, [], $progress);

        $progress->finish();
    }
}
