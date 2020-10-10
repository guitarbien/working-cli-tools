<?php

namespace App\Github;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Class Repository
 * @package App\Github
 */
class Repository
{
    private $header;

    /**
     * Repository constructor.
     */
    public function __construct()
    {
        $this->header = [
            'Authorization' => 'token ' . env('GITHUB_ACCESS_TOKEN'),
            'Accept' => 'application/vnd.github.inertia-preview+json',
        ];
    }

    /**
     * @return string
     */
    public function getNote(): string
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

        return collect($allRepoIssues)->filter(function ($item) use ($targetIssues) {
            return in_array($item['number'], $targetIssues);
        })->reduce(function ($carry, $item) {
            return $carry . vsprintf('- %s #%d' . PHP_EOL, [
                $item['title'],
                $item['number'],
            ]);
        }, '');
    }

    /**
     * @param  Release  $release
     * @return void
     * @throws Exception
     */
    public function postToGithub(Release $release): void
    {
        $endPoint = $this->getRepoEndpoint('releases');
        $result = Http::withHeaders($this->header)->post($endPoint, $release->toArray());

        if ($result->status() !== \Symfony\Component\HttpFoundation\Response::HTTP_CREATED) {
            throw Exception::createFail();
        }
    }

    /**
     * @param  string  $resource
     * @return string
     */
    private function getRepoEndpoint(string $resource): string
    {
        return vsprintf('https://api.github.com/repos/%s/%s/'.$resource, [
            config('github.owner'),
            config('github.repo'),
        ]);
    }

    /**
     * @param  string  $endPoint
     * @param  array  $query
     * @return Response
     */
    private function getFromGithub(string $endPoint, array $query = []): Response
    {
        return Http::withHeaders($this->header)->get($endPoint, $query);
    }
}
