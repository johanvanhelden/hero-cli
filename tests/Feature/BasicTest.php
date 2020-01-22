<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * A basic test.
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class BasicTest extends TestCase
{
    /** @test */
    public function it_has_a_proper_exit_code()
    {
        $this->withExceptionHandling();

        $this->artisan('recipe --help')
            ->assertExitCode(0);
    }
}
