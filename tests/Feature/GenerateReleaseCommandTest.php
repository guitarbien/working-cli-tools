<?php

namespace Tests\Feature;

use App\Github\Exception;
use App\Github\Github;
use Tests\TestCase;

/**
 * Class GenerateReleaseCommandTest
 * @package Tests\Feature
 */
class GenerateReleaseCommandTest extends TestCase
{
    public function testExecuteSuccess()
    {
        $this->mock(Github::class, function ($mock) {
            $mock->shouldReceive('createRelease');
        });

        $this->artisan('releasing master stage v1.0.0')
             ->expectsOutput('draft release was created with tag v1.0.0')
             ->assertExitCode(0);
    }

    public function testExecuteFailure()
    {
        $this->mock(Github::class, function ($mock) {
            $mock->shouldReceive('createRelease')
                 ->andThrow(Exception::class);
        });

        $this->artisan('releasing master stage v1.0.0')
             ->expectsOutput('draft release was created with tag v1.0.0')
             ->assertExitCode(0);
    }
}
