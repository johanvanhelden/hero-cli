<?php

namespace App\Helpers;

class Project
{
    public static function dockerPath(string $projectName): string
    {
        return env('DOCKER_PROJECT_ROOT') . DIRECTORY_SEPARATOR . $projectName;
    }

    public static function localPath(string $projectName): string
    {
        return env('LOCAL_PROJECT_ROOT') . DIRECTORY_SEPARATOR . $projectName;
    }
}
