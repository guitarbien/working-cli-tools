<?php

namespace App\Commands;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;

/**
 * Class GenerateReleaseCommands
 * @package App\Commands
 */
class GenerateReleaseCommands extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'release:note
                            {branch : the branch name which tag will set}
                            {tag    : tag name, just like v1.0.0}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'generate a new release note from cards in github projects';

    private $header;

    private const GITHUB_HOST = 'https://api.github.com';

    /**
     * GenerateReleaseCommands constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->header = [
            'Authorization' => 'token ' . env('GITHUB_ACCESS_TOKEN'),
            'Accept' => 'application/vnd.github.inertia-preview+json',
        ];
    }

    public function handle(): void
    {
        // get project info
        $projectResponse = $this->getFromGithub($this->getRepoEndpoint('projects'), [
            'state' => 'open',
        ]);

        // get column info
        $columnsResponse = $this->getFromGithub($projectResponse->json('0.columns_url'));

        $targetColumn = collect($columnsResponse->json())->filter(function ($item) {
            return $item['name'] === config('github.project_release_column');
        })->first();

        // get card info
        $cardsResponse = $this->getFromGithub($targetColumn['cards_url'], [
            'archived_state' => 'not_archived',
        ]);

        $targetIssues = collect($cardsResponse->json())->filter(function ($item) {
            return isset($item['content_url']);
        })->map(function ($item) {
            return last(explode('/', $item['content_url']));
        })->all();

        // get all issues' name
        $allRepoIssues = $this->getFromGithub($this->getRepoEndpoint('issues'), [
            'state' => 'closed',
        ])->json();

        $releaseNotes = collect($allRepoIssues)->filter(function ($item) use ($targetIssues) {
            return in_array($item['number'], $targetIssues);
        })->reduce(function ($carry, $item) {
            return $carry . vsprintf('- %s #%d' . PHP_EOL, [
                $item['title'],
                $item['number'],
            ]);
        }, '');

        // create a new release note
        $result = $this->postToGithub($this->getRepoEndpoint('releases'), [
            'tag_name' => $this->argument('tag'),
            'target_commitish' => $this->argument('branch'),
            'name' => 'regular release',
            'body' => $releaseNotes,
            'draft' => true,
            'prerelease' => true,
        ]);

        if ($result->status() !== \Symfony\Component\HttpFoundation\Response::HTTP_CREATED) {
            $this->info('create release fail');
        }

        $this->info($this->argument('tag') . ' draft release was created:' . PHP_EOL . $releaseNotes);
    }

    /**
     * @param  string  $resource
     * @return string
     */
    private function getRepoEndpoint(string $resource): string
    {
        return vsprintf(self::GITHUB_HOST . '/repos/%s/%s/' . $resource, [
            config('github.owner'),
            config('github.repo'),
        ]);
    }

    /**
     * @param  string  $uri
     * @param  array  $query
     * @return Response
     */
    private function getFromGithub(string $uri, array $query = []): Response
    {
        return Http::withHeaders($this->header)->get($uri, $query);
    }

    /**
     * @param  string  $uri
     * @param  array  $body
     * @return Response
     */
    private function postToGithub(string $uri, array $body): Response
    {
        return Http::withHeaders($this->header)->post($uri, $body);
    }
}
