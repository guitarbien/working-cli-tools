<?php

namespace App\Github;

/**
 * Class Release
 * @package App\Github
 */
class Release
{
    /** @var string */
    private $tagName;

    /** @var string */
    private $targetCommitish;

    /** @var string */
    private $name;

    /** @var string */
    private $body;

    /** @var bool */
    private $draft;

    /** @var bool */
    private $preRelease;

    /**
     * Release constructor.
     * @param  string  $tagName
     * @param  string  $targetCommitish
     * @param  string  $name
     * @param  string  $body
     * @param  bool  $draft
     * @param  bool  $preRelease
     */
    public function __construct(
        string $tagName,
        string $targetCommitish,
        string $name,
        string $body,
        bool $draft,
        bool $preRelease
    ) {
        $this->tagName = $tagName;
        $this->targetCommitish = $targetCommitish;
        $this->name = $name;
        $this->body = $body;
        $this->draft = $draft;
        $this->preRelease = $preRelease;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'tag_name'         => $this->tagName,
            'target_commitish' => $this->targetCommitish,
            'name'             => $this->name,
            'body'             => $this->body,
            'draft'            => $this->draft,
            'prerelease'       => $this->preRelease,
        ];
    }
}
