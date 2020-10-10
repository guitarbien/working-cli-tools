<?php

namespace App\Github;

/**
 * Class Github
 * @package App\Github
 */
final class Github
{
    /** @var Repository */
    private $repo;

    /**
     * UseCase constructor.
     * @param  Repository  $repo
     */
    public function __construct(Repository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param  string  $tag
     * @param  string  $branch
     * @throws Exception
     */
    public function createRelease(string $tag, string $branch): void
    {
        $releaseNote = new Release(
            $tag,
            $branch,
            'regular release',
            $this->repo->getNote(),
            true,
            true,
        );

        $this->repo->postToGithub($releaseNote);
    }

}
