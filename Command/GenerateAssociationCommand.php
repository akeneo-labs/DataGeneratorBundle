<?php

namespace Pim\Bundle\DataGeneratorBundle\Command;

use Pim\Bundle\DataGeneratorBundle\DependencyInjection\Configuration\AssociationGeneratorConfiguration;
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
class GenerateAssociationCommand extends AbstractGenerateCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:generate:associations-file')
            ->setDescription('Generate test product association')
            ->addArgument(
                'configuration-file',
                InputArgument::REQUIRED,
                'YAML configuration file'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function getGeneratorConfiguration()
    {
        return new AssociationGeneratorConfiguration();
    }
}
