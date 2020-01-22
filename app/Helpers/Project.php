<?php

namespace App\Helpers;

/**
 * Project helpers.
 */
class Project
{
    /**
     * Get the docker path for a given project.
     *
     * @param string $projectName
     *
     * @return string
     */
    public static function dockerPath(string $projectName)
    {
        return env('DOCKER_PROJECT_ROOT') . DIRECTORY_SEPARATOR . $projectName;
    }

    /**
     * Get the local path for a given project.
     *
     * @param string $projectName
     *
     * @return string
     */
    public static function localPath(string $projectName)
    {
        return env('LOCAL_PROJECT_ROOT') . DIRECTORY_SEPARATOR . $projectName;
    }
}
