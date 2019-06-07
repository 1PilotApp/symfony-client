<?php

namespace OnePilot\ClientBundle\Classes;

abstract class LogsFiles
{
    /** @var int Size in bytes (100MB) */
    const MAX_FILE_SIZE = 100000000;

    /**
     * match following formats
     *  [%datetime%] %channel%.%level%: %message% %context% %extra%
     *  [%datetime%+%offset%] %level% %message%
     */
    const LOG_PATTERN = '#\[(?<date>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})(?>[\+-]\d{4})?\]\s{1}(?>(?<channel>[^\s:\-]+)\.)?(?<level>\w+)(?>\:)?\s(?<message>.*)#';

    const LEVELS = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug',
    ];

    /** @var array intervals in minutes */
    const LOGS_OVERVIEW_INTERVALS = [
        30 * 24 * 60,
        7 * 24 * 60,
        1 * 24 * 60,
    ];

    /**
     * @param $logsDirectory
     * @param $environment
     *
     * @return array
     */
    public function getLogsFiles($logsDirectory, $environment)
    {
        $files = array_reverse(glob($logsDirectory . '/' . $environment . '-*.log'));

        if (file_exists($path = $logsDirectory . '/' . $environment . '.log')) {
            $files[] = $path;
        }

        return $files;
    }
}
