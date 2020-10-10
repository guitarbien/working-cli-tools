<?php

namespace App\Commands;

use App\Github\Exception;
use App\Github\Github;
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

    /**
     * @param  Github  $github
     */
    public function handle(Github $github): void
    {
        try {
            $github->createRelease($this->argument('tag'), $this->argument('branch'));
        } catch (Exception $e) {
            $this->info('create release fail');
        }

        $this->info('draft release was created with tag ' . $this->argument('tag'));
    }
}
