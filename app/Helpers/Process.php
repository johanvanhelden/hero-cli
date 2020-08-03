<?php

declare(strict_types=1);

namespace App\Helpers;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process as SymfonyProcess;

/**
 * Process helpers.
 */
class Process
{
    /** @var self */
    private static $instance;

    /** @var string */
    protected static $input;

    /** @var string */
    protected static $inDirectory;

    /** @var bool */
    protected static $allowFailure = false;

    /** @var bool */
    protected static $showOutput = false;

    /** @return self */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /** @return $this */
    public function input(string $value)
    {
        self::$input = $value;

        return $this;
    }

    /** @return $this */
    public function inDirectory(string $value)
    {
        self::$inDirectory = $value;

        return $this;
    }

    /** @return $this */
    public function allowFailure(bool $value = true)
    {
        self::$allowFailure = $value;

        return $this;
    }

    /** @return $this */
    public function showOutput(bool $value = true)
    {
        self::$showOutput = $value;

        return $this;
    }

    /**
     * Call the process and throw an exception if it fails.
     *
     * A wrapper around the symphony process to more easily customize the process for our purposes.
     */
    public function execute(array $command): SymfonyProcess
    {
        $process = new SymfonyProcess($command);

        // some processes could take a while, depending on how much work needs to be done
        $process->setTimeout(1 * 60 * 15);

        if (!empty(self::$input)) {
            $process->setInput(self::$input);
        }

        if (!empty(self::$inDirectory)) {
            $process->setWorkingDirectory(self::$inDirectory);
        }

        if (self::$showOutput) {
            $process->run(function ($type, $buffer): void {
                unset($type);

                echo $buffer;
            });
        } else {
            $process->run();
        }

        if (!self::$allowFailure && !$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process;
    }
}
