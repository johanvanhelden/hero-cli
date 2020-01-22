<?php

namespace App\Commands;

use App\Helpers\Process;
use App\Traits\SendsNotifications;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

/**
 * A command to clean up docker images and containers.
 */
class DockerCleanupCommand extends Command
{
    use SendsNotifications;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'docker:cleanup';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Cleans up docker images and containers';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->handleContainers();
        $this->handleImages();

        $finishedMessage = 'Finished cleaning up docker images and containers.';

        $this->notification($finishedMessage);
        $this->info($finishedMessage);
    }

    /**
     * Handles the removal of dangling containers.
     */
    private function handleContainers()
    {
        $this->info('Removing all dangling containers...');

        $exitedContainers = Process::getInstance()
            ->execute([
                'docker',
                'ps',
                '--filter',
                'status=dead',
                '--filter',
                'status=exited',
                '-aq',
            ])
            ->getOutput();

        if (empty($exitedContainers)) {
            $this->info('There are no containers to clean up.');

            return;
        }

        Process::getInstance()
            ->input($exitedContainers)
            ->execute([
                'xargs',
                '-r',
                'docker',
                'rm',
                '-v',
            ]);

        $this->info('Removed all dangling containers.');
    }

    /**
     * Handles the removal of dangling images.
     */
    private function handleImages()
    {
        $this->info('Removing all dangling images...');

        $exitedImages = Process::getInstance()
            ->execute([
                'docker',
                'images',
                '--no-trunc',
            ])
            ->getOutput();

        $cleanedList = Process::getInstance()
            ->input($exitedImages)
            ->allowFailure()
            ->execute([
                'grep',
                '<none>',
            ])
            ->getOutput();

        if (empty($cleanedList)) {
            $this->info('There are no images to clean up.');

            return;
        }

        $finalList = Process::getInstance()
            ->input($cleanedList)
            ->execute([
                'awk',
                '{ print $3 }',
            ])
            ->getOutput();

        Process::getInstance()
            ->input($finalList)
            ->execute([
                'xargs',
                '-r',
                'docker',
                'rmi',
            ]);

        $this->info('Removed all dangling images.');
    }

    /**
     * Define the command's schedule.
     *
     * @param Schedule $schedule
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function schedule(Schedule $schedule)
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
