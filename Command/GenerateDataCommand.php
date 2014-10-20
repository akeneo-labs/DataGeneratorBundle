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
                'entity-type',
                InputArgument::REQUIRED,
                'Type of entity to generate (product, association)'
            )
            ->addArgument(
                'amount',
                InputArgument::REQUIRED,
                'Number of entities to generate'
            )
            ->addArgument(
                'output-file',
                InputArgument::REQUIRED,
                'Target file where to generate the data'
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
            )
            ->addOption(
                'mandatory-attributes',
                'm',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'List of mandatory attributes for products (the identifier is always included)'
            )
            ->addOption(
                'delimiter',
                'c',
                InputOption::VALUE_REQUIRED,
                'Character delimiter used for the CSV file'
            )
            ->addOption(
                'force-value',
                'f',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Force the value of an attribute to the provided value. Syntax: attribute_code:value'
            )
            ->addOption(
                'start-index',
                'i',
                InputOption::VALUE_REQUIRED,
                'Define the start index value for the products sku definition.'
            )
            ->addOption(
                'categories-count',
                null,
                InputOption::VALUE_REQUIRED,
                'Average number of categories in which the product must be present. Set to 0 to have no category presence for products.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entities = [
            'product' => [
                'service' => 'pim_datagenerator.generator.product_csv',
                'options' => [
                        'values-number',
                        'values-number-standard-deviation',
                        'mandatory-attributes',
                        'delimiter',
                        'force-value',
                        'start-index',
                        'categories-count'
                ]
            ],
            'association' => [
                'service' => 'pim_datagenerator.generator.association_csv',
                'options' => [
                        'delimiter'
                ]
            ]
        ];

        $entityType = $input->getArgument('entity-type');
        $amount     = $input->getArgument('amount');
        $outputFile = $input->getArgument('output-file');

        if (!isset($entities[$entityType])) {
            $output->writeln(
                sprintf(
                    '<error>The entity type %s is not allowed. Only %s can be used.</error>',
                    $entityType,
                    implode(',', arrau_keys($entities))
                )
            );

            return 1;
        }

        $entityConfig = $entities[$entityType];

        $progress = $this->getHelperSet()->get('progress');

        if ($amount> 0) {
            $output->writeln(
                sprintf(
                    '<info>Generating %d instances of entity type %s to %s<info>',
                    $amount,
                    $entityType,
                    $outputFile
                )
            );

            $generator = $this->getContainer()->get($entityConfig['service']);

            $options = [];
            foreach ($entityConfig['options'] as $option) {
                $options[$option] = $input->getOption($option);
            }
            $progress->start($output, $amount);
            $generator->generate($amount, $outputFile, $progress, $options);
        }
    }
}
