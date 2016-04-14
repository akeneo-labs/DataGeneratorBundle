<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Pim\Bundle\DataGeneratorBundle\Writer\CsvWriter;
use Pim\Bundle\UserBundle\Entity\User;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Yaml;

/**
 * Generate native CSV file for job profile accesses. It gives all rights for every group in every job.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobProfileAccessGenerator implements GeneratorInterface
{
    const TYPE = 'job_profile_accesses';

    const JOB_PROFILE_ACCESSES_FILENAME = 'job_profile_accesses.csv';

    const JOB_PROFILE_ACCESSES = 'job_profile_accesses';

    /** @var CsvWriter */
    protected $writer;

    /**
     * @param CsvWriter $writer
     */
    public function __construct(CsvWriter $writer)
    {
        $this->writer = $writer;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $entitiesConfig, ProgressBar $progress, array $options = [])
    {
        $groups   = $options['user_groups'];
        $jobCodes = $options['job_codes'];

        $groupNames = [];
        foreach ($groups as $group) {
            if (User::GROUP_DEFAULT !== $group->getName()) {
                $groupNames[] = $group->getName();
            }
        }

        $data = [];
        foreach ($jobCodes as $jobCode) {
            $data[] = [
                'job_profile'         => $jobCode,
                'execute_job_profile' => implode(',', $groupNames),
                'edit_job_profile'    => implode(',', $groupNames),
            ];
        }

        $progress->advance();

        $this->writer
            ->setFilename(sprintf(
                '%s%s%s',
                $globalConfig['output_dir'],
                DIRECTORY_SEPARATOR,
                self::JOB_PROFILE_ACCESSES_FILENAME
            ))
            ->write($data);

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return self::TYPE === $type;
    }
}
