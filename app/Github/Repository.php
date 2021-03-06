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
     * @param  string  $projectColumn
     * @return string
     */
    public function getNote(string $projectColumn): string
    {
        // get project info
        $projectResponse = $this->getFromGithub($this->getRepoEndpoint('projects'), [
            'state' => 'open',
        ]);

        // get column info
        $columnsResponse = $this->getFromGithub($projectResponse->json('0.columns_url'));

        $targetColumn = collect($columnsResponse->json())->filter(function ($item) use ($projectColumn) {
            return $item['name'] === $projectColumn;
        })->first();

        // get card info
        $cardsResponse = $this->getFromGithub($targetColumn['cards_url'], [
            'archived_state' => 'not_archived',
        ]);

        return collect($cardsResponse->json())->filter(function ($item) {
            return isset($item['content_url']);
        })->map(function ($item) {
            // get issues' info
            return $this->getFromGithub($item['content_url'])->json();
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
        return vsprintf('https://api.github.com/repos/%s/' . $resource, [
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
