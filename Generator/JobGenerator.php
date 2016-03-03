<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Yaml;

/**
 * Job instances fixtures generator.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobGenerator
{
    const JOB_FILENAME = 'jobs.yml';

    const INTERNAL_JOBS_FILE = 'Resources/config/internal_jobs.yml';

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $config, ProgressBar $progress, array $options = [])
    {
        $jobs = $this->generateJobs($config);

        $normalizedJobs = $this->normalizeJobs($jobs);

        $normaizedJobs['jobs'] = array_merge(
            $normalizedJobs['jobs'],
            $this->getInternalJobs()
        );

        $this->writeYamlFile(
            $normalizedJobs,
            $globalConfig['output_dir'] . "/" . static::JOB_FILENAME
        );

        $progress->advance();

        return $jobs;
    }

    /**
     * Generate jobs objects
     *
     * @param array $jobsConfig
     *
     * @return JobInstance[]
     */
    protected function generateJobs(array $jobsConfig)
    {
        foreach ($jobsConfig as $jobCode => $jobConfig) {
            $job = $this->generateJob($jobCode, $jobConfig);
            $jobs[$job->getCode()] = $job;
        }

        return $jobs;
    }

    /**
     * Generate a job object from the data provided
     *
     * @param string $code
     * @param array  $config
     *
     * @return JobInstance
     */
    protected function generateJob($code, array $jobConfig)
    {
        $job = new JobInstance();
        $job->setCode($code);
        $job->setConnector($jobConfig['connector']);
        $job->setAlias($jobConfig['alias']);
        $job->setLabel($jobConfig['label']);
        $job->setType($jobConfig['type']);
        $job->setRawConfiguration($jobConfig['configuration']);

        return $job;
    }

    /**
     * Normalize jobs objects into a structured array
     *
     * @param Job[]
     *
     * @return array
     */
    protected function normalizeJobs(array $jobs)
    {
        $normalizedJobs = [];

        foreach ($jobs as $job) {
            $normalizedJobs[$job->getCode()] = $this->normalizeJob($job);
        }

        return [ "jobs" => $normalizedJobs ];
    }

    /**
     * Normalize job object into a structured array
     *
     * @param JobInstance
     *
     * @return array
     */
    protected function normalizeJob(JobInstance $job)
    {
        return [
            "connector"     => $job->getConnector(),
            "alias"         => $job->getAlias(),
            "label"         => $job->getLabel(),
            "type"          => $job->getType(),
            "configuration" => $job->getRawConfiguration()
        ];
    }

    /**
     * Get the internal jobs definition as an array
     *
     * @return array
     */
    protected function getInternalJobs()
    {
        $internalJobsPath = __DIR__.'/../'.static::INTERNAL_JOBS_FILE;
        $yamlParser = new Yaml\Parser();

        return $yamlParser->parse(file_get_contents($internalJobsPath));
    }

    /**
     * Write a YAML file
     *
     * @param array  $data
     * @param string $filename
     */
    protected function writeYamlFile(array $data, $filename)
    {
        $dumper = new Yaml\Dumper();
        $yamlData = $dumper->dump($data, 5, 0, true, true);

        file_put_contents($filename, $yamlData);
    }
}
