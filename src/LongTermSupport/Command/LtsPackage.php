<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

class LtsPackage extends Package
{
    /**
     * @var string
     */
    protected $status = self::STATUS_LTS;

    public function __construct(GithubRepo $repo, SkeletonPackage $skeleton)
    {
        $this->repo = $repo;
        $this->supportEnds = $skeleton->supportEnds();
        $this->normalizeSupportVersions($skeleton->getPackageConstraints($repo));
        $this->skeleton = $skeleton->getName();
    }
}
