<?php

namespace Pim\Bundle\DataGeneratorBundle\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Generates CSV products file
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GenerateProductCommand extends AbstractGenerateCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:generate:products-file')
            ->setDescription('Generate test products for PIM entities')
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

        $generator = $this->getContainer()->get('pim_data_generator.product_generator');

        $totalCount = $this->getTotalCount($config);

        $outputDir = $config['output_dir'];

        $output->writeln(
            sprintf('<info>Generating <comment>%d</comment> products', $totalCount)
        );

        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output, $totalCount);

        $generator->generate($config, $outputDir, $progress);

        $progress->finish();
    }
}
