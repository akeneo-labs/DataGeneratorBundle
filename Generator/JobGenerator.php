<?php

namespace Pim\Bundle\DataGeneratorBundle\Generator;

use Akeneo\Component\Batch\Model\JobInstance;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

/**
 * Job instances fixtures generator.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobGenerator implements GeneratorInterface
{
    const TYPE = 'jobs';

    const JOB_FILENAME = 'jobs.yml';

    const INTERNAL_JOBS_FILE = 'Resources/config/internal_jobs.yml';

    /**
     * {@inheritdoc}
     */
    public function generate(array $globalConfig, array $entitiesConfig, ProgressBar $progress, array $options = [])
    {
        $jobs = $this->generateJobs($entitiesConfig);

        $normalizedJobs = $this->normalizeJobs($jobs);
        if (!isset($normalizedJobs['jobs'])) {
            $normalizedJobs['jobs'] = [];
        }

        $normalizedJobs['jobs'] = array_merge(
            $normalizedJobs['jobs'],
            $this->getInternalJobs()
        );

        $this->writeYamlFile(
            $normalizedJobs,
            sprintf('%s%s%s', $globalConfig['output_dir'], DIRECTORY_SEPARATOR, self::JOB_FILENAME)
        );

        $progress->advance();

        return ['job_codes' => array_keys($normalizedJobs['jobs'])];
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
        $jobs = [];
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
     * @param array  $jobConfig
     *
     * @return JobInstance
     */
    protected function generateJob($code, array $jobConfig)
    {
        $job = new JobInstance();
        $job->setCode($code);
        $job->setConnector($jobConfig['connector']);
        $job->setJobName($jobConfig['alias']);
        $job->setLabel($jobConfig['label']);
        $job->setType($jobConfig['type']);

        if (isset($jobConfig['configuration'])) {
            $job->setRawParameters($jobConfig['configuration']);
        }

        return $job;
    }

    /**
     * Normalize jobs objects into a structured array
     *
     * @param JobInstance[] $jobs
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
     * @param JobInstance $job
     *
     * @return array
     */
    protected function normalizeJob(JobInstance $job)
    {
        $normalizeJob = [
            "connector" => $job->getConnector(),
            "alias"     => $job->getJobName(),
            "label"     => $job->getLabel(),
            "type"      => $job->getType()
        ];

        if (count($job->getRawParameters()) > 0) {
            $normalizeJob["configuration"] = $job->getRawParameters();
        }

        return $normalizeJob;
    }

    /**
     * Get the internal jobs definition as an array
     *
     * @return array
     */
    protected function getInternalJobs()
    {
        $internalJobsPath = sprintf(
            '%s%s..%s%s',
            __DIR__,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            self::INTERNAL_JOBS_FILE
        );
        $yamlParser = new Parser();

        return $yamlParser->parse(file_get_contents($internalJobsPath))['jobs'];
    }

    /**
     * Write a YAML file
     *
     * @param array  $data
     * @param string $filename
     */
    protected function writeYamlFile(array $data, $filename)
    {
        $dumper = new Dumper();
        $yamlData = $dumper->dump($data, 5, 0, true, true);

        file_put_contents($filename, $yamlData);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return self::TYPE === $type;
    }
}
