<?php

namespace App\Commands;

use App\Helpers\Process;
use App\Helpers\Project;
use App\Traits\SendsNotifications;
use Dotenv\Dotenv;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

class SetupProjectCommand extends Command
{
    use SendsNotifications;

    /** @var string */
    protected $signature = 'setup:project {project?}';

    /** @var string */
    protected $description = 'Sets up a given project';

    /** @var string */
    private $projectName;

    public function handle(): void
    {
        $this->projectName = $this->argument('project');
        if (empty($this->projectName)) {
            $this->projectName = basename(getcwd());
        }

        $this->info('Running the setup for ' . $this->projectName);

        if (!File::exists(Project::localPath($this->projectName))) {
            $this->error($this->projectName . ' is not a valid project');

            return;
        }

        $this->setEnvFile();

        $dotenv = Dotenv::createImmutable([Project::localPath($this->projectName)]);
        $dotenv->load();

        $this->createDatabaseUser();
        $this->createDatabase();

        $finishedMessage = 'Finished setup for ' . $this->projectName;

        $this->notification($finishedMessage);
        $this->info($finishedMessage);
    }

    private function createDatabaseUser(): void
    {
        $dbUser = env('DB_USERNAME');
        $dbPassword = env('DB_PASSWORD');

        $userSelect = Process::getInstance()
            ->execute([
                'docker',
                'exec',
                '-i',
                'dockerhero_db',
                'mysql',
                '-uroot',
                '-pdockerhero',
                '-e SELECT "MATCH FOUND" FROM mysql.user WHERE user = "' . $dbUser . '"',
            ])
            ->getOutput();

        if (strpos($userSelect, 'MATCH FOUND') !== false) {
            $this->info('The database user has already been created.');

            return;
        }

        $sqlCommands = [
            "CREATE USER '" . $dbUser . "'@'%' IDENTIFIED BY '" . $dbPassword . "'",
            "GRANT USAGE ON *.* TO '$dbUser'@'%' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0"
                . ' MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;',
            'GRANT ALL PRIVILEGES ON `' . $dbUser . "_%`.* TO '$dbUser'@'%';",
            'FLUSH PRIVILEGES;',
        ];

        foreach ($sqlCommands as $command) {
            Process::getInstance()
                ->execute([
                    'docker',
                    'exec',
                    '-i',
                    'dockerhero_db',
                    'mysql',
                    '-uroot',
                    '-pdockerhero',
                    '-pdockerhero',
                    "-e $command",
                ]);
        }
    }

    private function createDatabase(): void
    {
        $dbName = env('DB_DATABASE');
        $testsDbName = str_replace('_db', env('TESTS_DB_SUFFIX') . '_db', $dbName);

        $databases = Process::getInstance()
            ->execute([
                'docker',
                'exec',
                '-i',
                'dockerhero_db',
                'mysql',
                '-uroot',
                '-pdockerhero',
                '-e SHOW databases',
            ])
            ->getOutput();

        if (strpos($databases, $dbName) !== false) {
            $this->info('The database has already been created.');

            return;
        }

        $sqlCommands = [
            "CREATE DATABASE `$dbName`;",
            "CREATE DATABASE `$testsDbName`;",
        ];

        foreach ($sqlCommands as $command) {
            Process::getInstance()
                ->execute([
                    'docker',
                    'exec',
                    '-i',
                    'dockerhero_db',
                    'mysql',
                    '-uroot',
                    '-pdockerhero',
                    '-pdockerhero',
                    "-e $command",
                ]);
        }
    }

    private function setEnvFile(): void
    {
        if (File::exists(Project::localPath($this->projectName) . '/.env')) {
            $overwrite = $this->choice('A current .env file has been found, overwrite?', ['no', 'yes'], 'no');

            if ($overwrite == 'no') {
                $this->info('Not overwriting the .env file.');

                return;
            }

            $this->info('Overwriting the .env file.');
        }

        File::copy(
            Project::localPath($this->projectName) . '/.env.example',
            Project::localPath($this->projectName) . '/.env'
        );
    }
}
