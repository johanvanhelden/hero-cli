<?php

declare(strict_types=1);

namespace App\Commands;

use App\Helpers\Process;
use App\Traits\SendsNotifications;
use LaravelZero\Framework\Commands\Command;

class DockerCleanupCommand extends Command
{
    use SendsNotifications;

    /** @var string */
    protected $signature = 'docker:cleanup';

    /** @var string */
    protected $description = 'Cleans up docker images and containers';

    public function handle(): void
    {
        $this->handleContainers();
        $this->handleImages();

        $finishedMessage = 'Finished cleaning up docker images and containers.';

        $this->notification($finishedMessage);
        $this->info($finishedMessage);
    }

    private function handleContainers(): void
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

    private function handleImages(): void
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
}
