<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Facades\App;
use Symfony\Component\Yaml\Yaml;

class Recipe
{
    public static function processVariables(string $command, string $projectName): string
    {
        return str_replace('{localProjectPath}', Project::localPath($projectName), $command);
    }

    public static function getRecipesList(): array
    {
        return Yaml::parseFile(App::basePath('recipes.yml'));
    }
}
