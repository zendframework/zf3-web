<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

class BlacklistRepositoryFilter
{
    /**
     * List of repositories that should NOT be used ever.
     *
     * @var string[]
     */
    private $blacklist;

    public function __construct(array $blacklist = [])
    {
        $this->blacklist = $blacklist;
    }

    public function __invoke(string $name) : bool
    {
        return ! in_array($name, $this->blacklist, true);
    }
}
