<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

class NonZedRepositoryFilter
{
    /**
     * Non-matching repositories that should nonetheless be allowed
     *
     * @var string[]
     */
    private $whitelist;

    public function __construct(array $whitelist = [])
    {
        $this->whitelist = $whitelist;
    }

    public function __invoke(string $name) : bool
    {
        [$org, $repo] = explode('/', $name, 2);
        return in_array($name, $this->whitelist, true)
            || 'z' === $repo[0];
    }
}
