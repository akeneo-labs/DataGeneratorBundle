<?php

namespace Pim\Bundle\DataGeneratorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

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
                'output_dir',
                InputArgument::REQUIRED,
                'Target directory where to generate the data'
            )
            ->addOption(
                'product',
                'p',
                InputOption::VALUE_REQUIRED,
                'Number of products to generate'
            )
            ->addOption(
                'values-number',
                'a',
                InputOption::VALUE_REQUIRED,
                'Mean number of values to generate per products'
            )
            ->addOption(
                'values-number-standard-deviation',
                'd',
                InputOption::VALUE_REQUIRED,
                'Standard deviation for the number of values per product'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $outputDir = $input->getArgument('output_dir');

        $entities = [
            [
                'option'  => 'product',
                'service' => 'pim_datagenerator.generator.product_csv',
                'options' => [
                        'values-number', 'values-number-standard-deviation'
                    ]
                ]
        ];

        foreach ($entities as $entity) {
            $amount = (int) $input->getOption($entity['option']);

            if ($amount > 0) {
                $output->writeln(
                    sprintf(
                        '<info>Generating %d instances of entity type %s to %s directory<info>',
                        $amount,
                        $entity['option'],
                        $outputDir
                    )
                );
                $generator = $this->getContainer()->get($entity['service']);
                $options = [];
                foreach ($entity['options'] as $option) {
                    $options[$option] = $input->getOption($option);
                }
                $generator->generate($amount, $outputDir, $options);
            }
        }
    }
}
