<?php

namespace Goldfinch\Tinify\CronTasks;

use SilverStripe\CronTask\Interfaces\CronTask;

class TinifyCronTask implements CronTask
{
    /**
     * run this task every 5 minutes
     *
     * @return string
     */
    public function getSchedule()
    {
        return "*/5 * * * *";
    }

    /**
     *
     * @return void
     */
    public function process()
    {
        echo 'hello';
    }
}
