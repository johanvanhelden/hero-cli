<?php

namespace App\Helpers;

use Symfony\Component\Yaml\Yaml;

class Recipe
{
    public static function processVariables(string $command, string $projectName): string
    {
        return str_replace('{localProjectPath}', Project::localPath($projectName), $command);
    }

    public static function getRecipesList(): array
    {
        return Yaml::parseFile(base_path('recipes.yml'));
    }
}
