<?php

namespace Codeages\Biz\Framework\Scheduler\Service;

interface SchedulerService
{
    public function register($job);

    public function execute();

    public function deleteJobByName($name);

    public function deleteJob($id);

    public function findJobFiredsByJobId($jobId);

    public function findExecutingJobFiredByJobId($jobId);

    public function searchJobLogs($condition, $orderBy, $start, $limit);

    public function countJobLogs($condition);

    public function searchJobs($condition, $orderBy, $start, $limit);

    public function countJobs($condition);

    public function markTimeoutJobs();

<<<<<<< HEAD
    public function createErrorLog($job, $message, $trace);
=======
    public function error($jobFired, $message, $trace);

    public function createJobProcess($process);

    public function updateJobProcess($id, $process);
>>>>>>> d04b1f39ab48cab3c540008697af93e88904cf4c
}
