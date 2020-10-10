<?php

namespace Tests\Unit;

use App\Github\Release;
use Tests\TestCase;

/**
 * Class ReleaseTest
 * @package Tests\Unit
 */
class ReleaseTest extends TestCase
{
    public function testReleaseObject()
    {
        // arrange
        $release = new Release(
            'v1.0.0',
            'master',
            'regular release',
            'body',
            true,
            true,
        );

        // act
        $result = $release->toArray();

        // assert
        $this->assertSame([
            'tag_name'         => 'v1.0.0',
            'target_commitish' => 'master',
            'name'             => 'regular release',
            'body'             => 'body',
            'draft'            => true,
            'prerelease'       => true,
        ], $result);
    }
}
