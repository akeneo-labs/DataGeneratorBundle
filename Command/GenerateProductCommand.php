<?php

namespace Pim\Bundle\DataGeneratorBundle\Command;

use Pim\Bundle\DataGeneratorBundle\Configuration\ProductGeneratorConfiguration;
use Pim\Bundle\DataGeneratorBundle\DummyJobRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobExecution;

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
        $config = $this->getConfiguration($configFile, new ProductGeneratorConfiguration());

        $outputDir = $config['output_dir'];
        $this->checkOutputDirExists($outputDir);

        $productStep = $this->getContainer()->get('pim_data_generator.step.generate_product');
        $productReader = $this->getContainer()->get('pim_data_generator.reader.generated_product');
        $productWriter = $this->getContainer()->get('pim_base_connector.writer.file.csv_product');
        $productProcessor = $this->getContainer()->get('pim_base_connector.processor.product_to_flat_array');

        $jobExecution = new JobExecution();
        $stepExecution = new StepExecution("generate_products", $jobExecution);

        $productStep->setEventDispatcher($this->getContainer()->get('event_dispatcher'));
        $productStep->setJobRepository(new DummyJobRepository());

        $count = $config['entities']['products']['count'];

        $productReader->setItemCount($count);
        $productWriter->setFilePath($config['output_dir'].'/products.csv');

        $productProcessor->setChannel('ecommerce');

        $output->writeln(sprintf('<info>Generating <comment>%d</comment> products', $count));
        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output, $count);

        $productStep->execute($stepExecution);

        $progress->finish();
    }
}
