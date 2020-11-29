<?php

namespace App\Commands;

use App\Github\Exception;
use App\Github\Github;
use LaravelZero\Framework\Commands\Command;

/**
 * Class GenerateReleaseCommand
 * @package App\Commands
 */
class GenerateReleaseCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'releasing
                            {branch        : the branch name which the tag will set}
                            {projectColumn : the project column name which matched by the branch}
                            {tag           : tag name, just like v1.0.0}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'generate a new release note from cards in github projects';

    /**
     * @param  Github  $github
     */
    public function handle(Github $github): void
    {
        try {
            $github->createRelease($this->argument('tag'), $this->argument('branch'), $this->argument('projectColumn'));
        } catch (Exception $e) {
            $this->info('create release fail');
        }

        $this->info('draft release was created with tag ' . $this->argument('tag'));
    }
}
