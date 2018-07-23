<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

use DateTimeInterface;

class SecurityPackage extends Package
{
    /**
     * @var string
     */
    protected $status = self::STATUS_SECURITY;

    public function __construct(GithubRepo $repo, Tag $ltsTag, DateTimeInterface $expires)
    {
        $this->repo = $repo;
        $this->normalizeSupportVersions([$ltsTag->getName()]);
        $this->supportEnds = $expires;
    }
}
