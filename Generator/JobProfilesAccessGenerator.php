<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\UserBundle\Entity\Group;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Yaml;

/**
 * Generate native YAML file for job profile accesses. It gives all rights for every group in every job.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobProfilesAccessGenerator implements GeneratorInterface
{
    const JOB_PROFILE_ACCESSES_FILENAME = 'job_profile_accesses.yml';

    const JOB_PROFILE_ACCESSES = 'job_profile_accesses';

    /** @var Group[] */
    protected $groups;

    /** @var JobInstance[] */
    protected $jobs;

    /**
     * {@inheritdoc}
     */
    public function generate(array $config, $outputDir, ProgressHelper $progress, array $options = [])
    {
        $this->groups = $options['groups'];
        $this->jobs   = $options['jobs'];

        $data = [];
        foreach ($this->jobs as $job) {
            $jobCode = $job->getCode();
            $data[$jobCode] = [
                'executeJobProfile' => [],
                'editJobProfile'    => [],
            ];
            foreach ($this->groups as $group) {
                if ('all' !== $group->getName()) {
                    $data[$jobCode]['executeJobProfile'][] = $group->getName();
                    $data[$jobCode]['editJobProfile'][]    = $group->getName();
                }
            }
        }

        $assetCategoryAccesses = [self::JOB_PROFILE_ACCESSES => $data];

        $progress->advance();

        $this->writeYamlFile($assetCategoryAccesses, $outputDir);
    }

    /**
     * Write a YAML file
     *
     * @param array  $data
     * @param string $outputDir
     */
    protected function writeYamlFile(array $data, $outputDir)
    {
        $dumper = new Yaml\Dumper();
        $yamlData = $dumper->dump($data, 3, 0, true, true);

        file_put_contents($outputDir.'/'.self::JOB_PROFILE_ACCESSES_FILENAME, $yamlData);
    }
}
