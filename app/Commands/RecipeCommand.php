<?php

namespace App\Commands;

use App\Helpers\Process;
use App\Helpers\Project;
use App\Helpers\Recipe;
use App\Traits\SendsNotifications;
use Exception;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

/**
 * Runs a recipe.
 */
class RecipeCommand extends Command
{
    use SendsNotifications;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'recipe {recipe} {project?}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Run\'s a given recipe for a given project';

    /**
     * The name of the project.
     *
     * @var string
     */
    private $projectName;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$this->setAndValidateArguments()) {
            return;
        }

        $this->info('Running the "' . $this->recipeName . '" recipe for ' . $this->projectName);

        $commandsToRun = $this->getCommandsToRun();

        if ($this->isDockerheroNeededForACommand($commandsToRun) && !$this->isDockerheroRunning()) {
            $this->error('Dockerhero is currently not running');

            return;
        }

        foreach ($commandsToRun as $commandToRun) {
            $this->handleCommand($commandToRun);
        }

        $finishedMessage = 'Finished "' . $this->recipeName . '" recipe for ' . $this->projectName;

        $this->notification($finishedMessage);
        $this->info($finishedMessage);
    }

    /**
     * Validate and sets the passed arguments.
     *
     * @return bool
     */
    private function setAndValidateArguments()
    {
        $this->recipeName = $this->argument('recipe');
        if (empty($this->recipeName)) {
            $this->error('Missing recipe name');

            return false;
        }

        $this->projectName = $this->argument('project');
        if (empty($this->projectName)) {
            $this->projectName = basename(getcwd());
        }

        if (!File::exists(Project::localPath($this->projectName))) {
            $this->error($this->projectName . ' is not a valid project');

            return false;
        }

        return true;
    }

    /**
     * Returns a list of commands to run.
     *
     * @return array
     */
    private function getCommandsToRun()
    {
        $recipes = Recipe::getRecipesList();
        if (!isset($recipes[$this->recipeName])) {
            $this->error('No "' . $this->recipeName . '" recipe found');

            return;
        }

        return $recipes[$this->recipeName];
    }

    /**
     * Determines if Dockerhero is running.
     *
     * @return bool
     */
    private function isDockerheroRunning()
    {
        $output = Process::getInstance()
            ->allowFailure(true)
            ->execute(['docker', 'inspect', '-f', '{{.State.Running}}', 'dockerhero_workspace'])
            ->getOutput();

        return trim($output) == 'true';
    }

    /**
     * Determines if Dockerhero is needed for a command.
     *
     * @array $commands
     *
     * @return bool
     */
    private function isDockerheroNeededForACommand(array $commands)
    {
        $hasDockerCommand = array_filter($commands, function ($item) {
            return $item['environment'] == 'docker';
        });

        return !empty($hasDockerCommand);
    }

    /**
     * Handles the exection of a command.
     *
     * @param array $commandToRun
     */
    private function handleCommand(array $commandToRun)
    {
        $environment = $commandToRun['environment'];
        $command = Recipe::processVariables($commandToRun['command'], $this->projectName);
        $path = null;
        $allowFailure = false;
        $showOutput = false;

        if (!empty($commandToRun['path'])) {
            $path = Recipe::processVariables($commandToRun['path'], $this->projectName);
        }

        if (!empty($commandToRun['allow_failure'])) {
            $allowFailure = $commandToRun['allow_failure'];
        }

        if (!empty($commandToRun['show_output'])) {
            $showOutput = $commandToRun['show_output'];
        }

        switch ($environment) {
            case 'docker':
                $this->runDockerCommand($command, $allowFailure, $showOutput);
                break;

            case 'local':
                if (empty($path)) {
                    $path = Project::localPath($this->projectName);
                }

                $this->runLocalCommand($command, $path, $allowFailure, $showOutput);
                break;

            default:
                throw new Exception('Unable to handle the command for the ' . $environment . ' environment');
                break;
        }
    }

    /**
     * Runs a docker command.
     *
     * @param string $command
     * @param bool   $allowFailure
     * @param bool   $showOutput
     */
    private function runDockerCommand($command, $allowFailure, $showOutput)
    {
        $this->info('Running docker command "' . $command . '" for ' . $this->projectName);

        Process::getInstance()
            ->showOutput($showOutput)
            ->allowFailure($allowFailure)
            ->execute([
                'docker',
                'exec',
                '-i',
                '--user=dockerhero',
                'dockerhero_workspace',
                'bash',
                '-c',
                'cd ' . Project::dockerPath($this->projectName) . ' && ' . $command,
            ]);
    }

    /**
     * Runs a local command.
     *
     * @param string $command
     * @param string $path
     * @param bool   $allowFailure
     * @param bool   $showOutput
     */
    private function runLocalCommand($command, $path, $allowFailure, $showOutput)
    {
        $this->info('Running local command "' . $command . '" for ' . $this->projectName);

        $command = Recipe::processVariables($command, $this->projectName);
        $path = Recipe::processVariables($path, $this->projectName);

        $commandParts = explode(' ', $command);

        Process::getInstance()
            ->inDirectory($path)
            ->showOutput($showOutput)
            ->allowFailure($allowFailure)
            ->execute($commandParts);
    }
}
