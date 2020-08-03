<?php

namespace Tests\Feature;

use Tests\TestCase;

class BasicTest extends TestCase
{
    /** @test */
    public function it_has_a_proper_exit_code(): void
    {
        $this->artisan('recipe --help')
            ->assertExitCode(0);
    }
}
