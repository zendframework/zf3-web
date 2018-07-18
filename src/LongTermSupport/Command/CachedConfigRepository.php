<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

class CachedConfigRepository implements RepositoryInterface
{
    use GraphqlDataProcessingTrait;

    /**
     * Original cached data
     *
     * @var array
     */
    private $cache;

    /**
     * @var GithubRepo[]
     */
    private $data = [];

    public function __construct(array $cacheData, array $filterCriteria = [])
    {
        $this->cache = $cacheData;
        $this->assignCriteriaCallbacks($filterCriteria);
    }

    public function getAllRepos() : iterable
    {
        if ([] !== $this->data) {
            return $this->data;
        }

        $this->data = $this->processData($this->cache);
        return $this->data;
    }
}
