<?php

namespace Pim\Bundle\DataGeneratorBundle;

use Akeneo\Bundle\BatchBundle\Job\JobRepositoryInterface;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

/**
 * Dummy Job repository
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DummyJobRepository implements JobRepositoryInterface
{
    /**
     * Create a JobExecution object
     *
     * @param JobInstance $job
     *
     * @return JobExecution
     */
    public function createJobExecution(JobInstance $job)
    {
    }

    /**
     * Update a JobExecution
     *
     * @param JobExecution $jobExecution
     *
     * @return JobExecution
     */
    public function updateJobExecution(JobExecution $jobExecution)
    {
    }

    /**
     * Update a StepExecution
     *
     * @param StepExecution $stepExecution
     *
     * @return StepExecution
     */
    public function updateStepExecution(StepExecution $stepExecution)
    {
    }
}
