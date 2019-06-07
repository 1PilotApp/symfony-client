<?php

namespace OnePilot\ClientBundle\Classes;

use SplFileObject;

class LogsOverview extends LogsFiles
{
    private $intervalDates = [];
    private $overview = [];

    /** @var string */
    private $logsDirectory;

    /** @var string */
    private $environment;

    public function __construct(string $logsDirectory, string $environment)
    {
        $this->logsDirectory = $logsDirectory;
        $this->environment = $environment;
    }

    /** @return array */
    public function get()
    {
        foreach (self::LOGS_OVERVIEW_INTERVALS as $interval) {
            $this->intervalDates[$interval] = date('Y-m-d H:i:s', strtotime('-' . $interval . ' minutes'));
        }

        $files = $this->getLogsFiles($this->logsDirectory, $this->environment);

        foreach ($files as $filePath) {
            $file = new SplFileObject($filePath, 'r');
            $this->logsOverviewOfFile($file);
        }

        $overview = [];

        foreach ($this->overview as $interval => $levels) {
            $overview[$interval] = [];

            foreach ($levels as $level => $total) {
                $overview[$interval][] = [
                    'level' => $level,
                    'total' => $total,
                ];
            }
        }

        return $overview;
    }

    private function logsOverviewOfFile(SplFileObject $file)
    {
        while (!$file->eof()) {
            $line = $file->fgets();

            if (!isset($line[0]) || $line[0] != '[') {
                continue;
            }

            if (!preg_match(self::LOG_PATTERN, $line, $matches)) {
                continue;
            }

            if (empty($matches['date']) || empty($matches['level'])) {
                continue;
            }

            foreach ($this->intervalDates as $interval => $date) {
                if ($matches['date'] >= $date) {
                    $this->incrementLogsOverview($interval, $matches['level']);
                }
            }
        }
    }

    private function incrementLogsOverview($interval, $level)
    {
        $level = strtolower($level);

        if (!in_array($level, self::LEVELS)) {
            return;
        }

        if (!isset($this->overview[$interval][$level])) {
            $this->overview[$interval][$level] = 0;
        }

        $this->overview[$interval][$level]++;
    }
}
