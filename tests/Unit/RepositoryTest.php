<?php

namespace Tests\Unit;

use App\Github\Exception;
use App\Github\Release;
use App\Github\Repository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Class RepositoryTest
 * @package Tests\Unit
 */
class RepositoryTest extends TestCase
{
    public function testPostToGithubSuccess()
    {
        // arrange
        Config::set('github.repo', 'abc/def');
        Config::set('github.project_release_column', 'Done');

        Http::fake([
            'api.github.com/repos/abc/def/releases' => Http::response(null, 201),
        ]);

        $repo = new Repository();

        // act
        $repo->postToGithub(new Release(
            'v1.0.0',
            'master',
            'regular release',
            'body',
            true,
            true,
        ));

        // assert
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.github.com/repos/abc/def/releases' &&
                $request['tag_name'] === 'v1.0.0' &&
                $request['target_commitish'] === 'master' &&
                $request['name'] === 'regular release' &&
                $request['body'] === 'body' &&
                $request['draft'] === true &&
                $request['prerelease'] === true;
        });
    }

    public function testPostToGithubFailure()
    {
        // arrange
        Config::set('github.repo', 'abc/def');

        Http::fake([
            'api.github.com/repos/abc/def/releases' => Http::response(null, 403),
        ]);

        $repo = new Repository();

        // assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('新增失敗');

        // act
        $repo->postToGithub(new Release(
            'v1.0.0',
            'master',
            'regular release',
            'body',
            true,
            true,
        ));
    }

    public function testGetNote()
    {
        // arrange
        Config::set('github.repo', 'abc/def');
        Config::set('github.project_release_column', 'Done');

        Http::fake([
            'api.github.com/*' => Http::sequence()
                                    ->push([['columns_url' => 'https://api.github.com/projects/1002604/columns']], 200)
                                    ->push([['name' => 'Done', 'cards_url' => 'https://api.github.com/projects/columns/367/cards']], 200)
                                    ->push([['content_url' => 'https://api.github.com/repos/abc/def/issues/3']], 200)
                                    ->push(['title' => 'testing', 'number' => 3], 200)
        ]);

        $repo = new Repository();

        // act
        $noteBody = $repo->getNote('Done');

        // assert
        $this->assertEquals('- testing #3' . PHP_EOL, $noteBody);
    }
}
