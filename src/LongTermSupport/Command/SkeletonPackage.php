<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

use DateInterval;
use InvalidArgumentException;

class SkeletonPackage extends Package
{
    /**
     * @var Tag
     */
    private $lts;

    /**
     * @var array(string, string[])
     */
    private $requirements = [];

    protected $status = self::STATUS_LTS;

    public function __construct(GithubRepo $repo, Tag $lts, array $requirements)
    {
        $this->repo = $repo;
        $this->lts = $lts;
        $this->skeletons = [$repo->getName()];
        $this->supportEnds = $lts->getDate()->add(new DateInterval('P2Y'));
        $this->processRequirements($requirements);
        $this->versions = [$lts->getMinorVersion()];
    }

    public function getPackageConstraints(GithubRepo $repo) : array
    {
        if (! $this->isPackageInSkeleton($repo)) {
            throw new InvalidArgumentException(sprintf(
                'Package %s is not listed for the skeleton project %s; please run'
                . ' %s::isPackageInSkeleton() prior to calling the method %s::getPackageConstraints()',
                $repo->getName(),
                $this->getName(),
                __CLASS__,
                __CLASS__
            ));
        }

        return $this->requirements[$repo->getName()];
    }

    public function isPackageInSkeleton(GithubRepo $repo) : bool
    {
        return array_key_exists($repo->getName(), $this->requirements);
    }

    private function processRequirements(array $requirements) : void
    {
        foreach ($requirements as $package => $constraintString) {
            // Do not worry about non-ZF packages
            if (! preg_match('#^(zendframework|zfcampus)/#', $package)) {
                continue;
            }

            $this->requirements[$package] = array_filter(
                array_unique(preg_split('/[^\d\.]/', $constraintString)),
                function ($constraint) {
                    return ! empty($constraint);
                }
            );
        }
    }
}
