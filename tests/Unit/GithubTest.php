<?php

namespace Tests\Unit;

use App\Github\Github;
use App\Github\Release;
use App\Github\Repository;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Class GithubTest
 * @package Tests\Unit
 */
class GithubTest extends TestCase
{
    public function testCreateReleaseNote(): void
    {
        // arrange
        /** @var Repository|MockInterface $repo */
        $repo = $this->spy(Repository::class);

        $github = new Github($repo);

        // act
        $github->createRelease('v1.0.0', 'master');

        // assert
        $repo->shouldHaveReceived('getNote');
        $repo->shouldHaveReceived('postToGithub')->with(Release::class);
    }
}
