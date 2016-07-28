<?php

namespace Pim\Bundle\DataGeneratorBundle\Command;

use Pim\Bundle\DataGeneratorBundle\Configuration\AssociationGeneratorConfiguration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates CSV association file
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GenerateAssociationCommand extends AbstractGenerateCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:generate:associations-file')
            ->setDescription('Generate test product and group associations')
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

        $globalConfig = $this->getConfiguration($configFile, new AssociationGeneratorConfiguration());

        $generator = $this->getContainer()->get('pim_data_generator.generator.association');

        $outputDir = $globalConfig['output_dir'];
        $this->checkOutputDirExists($outputDir);

        $output->writeln(
            sprintf(
                '<info>Generating associations in the <comment>%s</comment> directory</info>',
                $outputDir
            )
        );

        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output);

        $generator->generate($globalConfig, $globalConfig['entities']['associations'], $progress);

        $progress->finish();
    }
}
