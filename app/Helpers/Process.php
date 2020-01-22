<?php

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

    /**
     * Get the instance of the process.
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Set the input property.
     *
     * @param string $value
     */
    public function input(string $value)
    {
        self::$input = $value;

        return $this;
    }

    /**
     * Set the inDirectory property.
     *
     * @param string $value
     */
    public function inDirectory(string $value)
    {
        self::$inDirectory = $value;

        return $this;
    }

    /**
     * Set the allowFailure property.
     *
     * @param bool $value
     */
    public function allowFailure(bool $value = true)
    {
        self::$allowFailure = $value;

        return $this;
    }

    /**
     * Set the showOutput property.
     *
     * @param bool $value
     */
    public function showOutput(bool $value = true)
    {
        self::$showOutput = $value;

        return $this;
    }

    /**
     * Call the process and throw an exception if it fails.
     *
     * A wrapper around the symphony process to more easily customize the process for our purposes.
     *
     * @param array $command
     *
     * @return SymfonyProcess
     */
    public function execute(array $command)
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
            $process->run(function ($type, $buffer) {
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
