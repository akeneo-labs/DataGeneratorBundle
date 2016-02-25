<?php

namespace Pim\Bundle\DataGeneratorBundle\Command;

use Pim\Bundle\DataGeneratorBundle\Configuration\ProductGeneratorConfiguration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $globalConfig = $this->getConfiguration($configFile, new ProductGeneratorConfiguration());

        $outputDir = $globalConfig['output_dir'];
        $this->checkOutputDirExists($outputDir);

        $productGenerator = $this->getContainer()->get('pim_data_generator.generator.product');

        $count = $globalConfig['entities']['products']['count'];
        $output->writeln(sprintf('<info>Generating <comment>%d</comment> products', $count));
        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output, $count);
        $productGenerator->generate($globalConfig, $globalConfig['entities']['products'], $progress);
        $progress->finish();

        if (isset($globalConfig['entities']['product_drafts'])) {
            $count = $globalConfig['entities']['product_drafts']['count'];
            $draftGenerator = $this->getContainer()->get('pim_data_generator.generator.product_draft');
            $output->writeln(sprintf('<info>Generating <comment>%d</comment> product drafts', $count));
            $progress = $this->getHelperSet()->get('progress');
            $progress->start($output, $count);
            $draftGenerator->generate($globalConfig, $globalConfig['entities']['product_drafts'], $progress);
            $progress->finish();
        }
    }
}
