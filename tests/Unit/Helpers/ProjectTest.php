<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use App\Helpers\Project;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    /** @test */
    public function it_can_determine_the_local_path(): void
    {
        $this->assertEquals(
            '/home/test/projects/test-project',
            Project::localPath('test-project')
        );
    }

    /** @test */
    public function it_can_determine_the_docker_path(): void
    {
        $this->assertEquals(
            '/var/www/projects/test-project',
            Project::dockerPath('test-project')
        );
    }
}
