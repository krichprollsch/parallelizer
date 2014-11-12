<?php
/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  13/12/12 11:25
 */

namespace Parallelizer;

use Symfony\Component\Process\Process;

class Parallelizer
{
    const DEFAULT_MAX_PROCESSES = 2;
    const DEFAULT_SLEEP_DURATION = 25000;

    protected $max_processes;
    protected $processes;
    protected $sleep_duration;

    public function __construct(
        $max_processes = self::DEFAULT_MAX_PROCESSES,
        $sleep_duration = self::DEFAULT_SLEEP_DURATION
    ) {
        $this->sleep_duration = $sleep_duration;
        $this->max_processes = $max_processes;
        $this->processes = array();
    }

    public function add(Process $process, $identifier = null)
    {
        $this->processes[$identifier ?: uniqid()] = $process;
    }

    public function run($callback = null)
    {
        if (!is_null($callback) &&
            !is_callable($callback)
        ) {
            throw new \InvalidArgumentException('Callback is not callable');
        }

        try {
            while (true) {
                if (!$this->startNext($callback)) {
                    $this->applyTerminated();
                    usleep($this->sleep_duration);
                }
            }
        } catch ( \RuntimeException $ex ) {
            //no more process to start
        }

        $this->wait();
    }

    public function countRunners()
    {
        $runners=0;
        foreach ($this->processes as $process) {
            if ($process->isRunning()) {
                $runners++;
            }
        }

        return $runners;
    }

    protected function startNext($callback = null)
    {
        if (!($process=$this->getNext())) {
            throw new \RuntimeException('No process to start');
        }
        if ($this->countRunners() >= $this->max_processes) {

            return false;
        }
        $process->start($callback);

        return true;
    }

    protected function getNext()
    {
        foreach ($this->processes as $process) {
            /* @var $process Process */
            if (is_null($process->getExitCode()) && !$process->isRunning()) {
                return $process;
            }
        }

        return false;
    }

    public function getProcesses()
    {
        return $this->processes;
    }

    protected function applyTerminated()
    {
        $this->getTerminated();
    }

    protected function wait()
    {
        do {
            $continue = $this->countRunners() > 0;
            $this->applyTerminated();
            if ($continue) {
                usleep($this->sleep_duration);
            }
        } while ($continue);
    }

    protected function getTerminated()
    {
        $terminated = array();
        foreach ($this->processes as $key => $process) {
            /* @var $process Process */
            if (!is_null($process->getExitCode())) {
                unset($this->processes[$key]);
                $terminated[] = $process;
            }
        }

        return $terminated;
    }
}
