<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

interface RepositoryInterface
{
    /**
     * Should return an iterable with each item a GithubRepo
     */
    public function getAllRepos() : iterable;
}
