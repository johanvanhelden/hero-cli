<?php

namespace App\Helpers;

use Symfony\Component\Yaml\Yaml;

/**
 * Recipe helpers.
 */
class Recipe
{
    /**
     * Processes a command by replacing variables.
     *
     * @param string $command
     * @param string $projectName
     *
     * @return string
     */
    public static function processVariables(string $command, string $projectName)
    {
        $command = str_replace('{localProjectPath}', Project::localPath($projectName), $command);

        return $command;
    }

    /**
     * Returns the recipes list.
     *
     * @return array
     */
    public static function getRecipesList()
    {
        return Yaml::parseFile(base_path('recipes.yml'));
    }
}
