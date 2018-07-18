<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use JsonSerializable;
use UnexpectedValueException;

class Package implements JsonSerializable
{
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_LTS = 'LTS';
    public const STATUS_SECURITY = 'SECURITY';

    /**
     * @var string
     */
    protected $status = self::STATUS_ACTIVE;

    /**
     * @var GithubRepo
     */
    protected $repo;

    /**
     * @var string[]
     */
    protected $skeletons = [];

    /**
     * @var ?DateTimeInterface
     */
    protected $supportEnds;

    /**
     * @var string[]
     */
    protected $versions = [];

    private $validStatuses = [
        self::STATUS_ACTIVE,
        self::STATUS_LTS,
        self::STATUS_SECURITY,
    ];

    public function __construct(GithubRepo $repo, array $skeletons = [])
    {
        $this->repo = $repo;

        [] === $skeletons
            ? $this->determineSupportVersions()
            : $this->calculateLtsValues($skeletons);
    }

    public function jsonSerialize()
    {
        $supportEnds = $this->supportEnds();
        return [
            'name' => $this->getName(),
            'skeletons' => $this->getSkeletons(),
            'status' => $this->status,
            'support_ends' => $supportEnds ? $supportEnds->format('Y-m-d') : 'N/A',
            'url'  => $this->getUrl(),
            'versions' => $this->getVersions(),
        ];
    }

    public function getSkeletons() : array
    {
        return $this->skeletons;
    }

    public function getName() : string
    {
        return $this->repo->getName();
    }

    public function supportEnds() : ?DateTimeInterface
    {
        return $this->supportEnds;
    }

    public function getUrl()
    {
        return sprintf('https://github.com/%s', $this->getName());
    }

    public function getVersions() : array
    {
        return $this->versions;
    }

    private function normalizeSupportVersions(array $versions) : void
    {
        // Ensure all versions are in <major>.<minor> format
        $versions = array_map(function ($version) {
            if (preg_match('/^\d+\.\d+$/', $version)) {
                return $version;
            }
            if (preg_match('/^\d+$/', $version)) {
                return sprintf('%d.0', $version);
            }
            $parts = explode('.', $version);
            return vsprintf('%d.%d', array_slice($parts, 0, 2));
        }, $versions);

        // Ensure only the most recent minor version for a major release is listed,
        // and that no 0.X versions are present.
        $this->versions = array_reduce($versions, function ($filtered, $version) {
            // Skip 0.X versions.
            if ('0' === $version[0]) {
                return $filtered;
            }

            if ([] === $filtered) {
                array_push($filtered, $version);
                return $filtered;
            }

            if (in_array($version, $filtered, true)) {
                return $filtered;
            }

            foreach ($filtered as $index => $compare) {
                if ($compare[0] !== $version[0]) {
                    continue;
                }

                if (version_compare($compare, $version, 'gt')) {
                    return $filtered;
                }

                $filtered[$index] = $version;
                return $filtered;
            }

            array_push($filtered, $version);
            return $filtered;
        }, []);
    }

    /**
     * @param SkeletonPackage[] $skeletons
     * @throws InvalidArgumentException See validateSkeletons() for details
     */
    private function calculateLtsValues(array $skeletons) : void
    {
        $this->validateSkeletons($skeletons);

        $today = new DateTimeImmutable('now');
        $this->supportEnds = array_reduce($skeletons, function ($date, $skeleton) use ($today) {
            $supportDate = $skeleton->supportEnds();
            if ($supportDate < $today) {
                return $date;
            }

            if (! $date) {
                return $supportDate;
            }

            return $supportDate < $date ? $date : $supportDate;
        }, null);

        if (null === $this->supportEnds) {
            $this->determineSupportVersions();
            return;
        }

        $map = array_reduce($skeletons, function ($map, $skeleton) {
            if (! $skeleton->isPackageInSkeleton($this->repo)) {
                return $map;
            }
            $map[$skeleton->getName()] = $skeleton->getPackageConstraints($this->repo);
            return $map;
        }, []);

        $this->status = self::STATUS_LTS;
        $this->skeletons = array_keys($map);
        $this->normalizeSupportVersions(array_reduce(array_values($map), function ($versions, $constraints) {
            return array_merge($versions, $constraints);
        }, []));
    }

    private function determineSupportVersions() : void
    {
        $interval = new DateInterval('P1Y');
        $today = new DateTimeImmutable('now');
        $latestMajor = $this->repo->getMostRecentMajorRelease();

        if (! $latestMajor) {
            $this->status = self::STATUS_ACTIVE;
            $this->normalizeSupportVersions([
                $this->repo->getMostRecentMinorRelease()->getName()
            ]);
            return;
        }

        $previousMinor = $this->repo->getPreviousMinorRelease($latestMajor);
        if (! $previousMinor
            || $latestMajor->getDate()->add($interval) < $today
        ) {
            $this->status = self::STATUS_ACTIVE;
            $this->normalizeSupportVersions([
                $this->repo->getMostRecentMinorRelease()->getName()
            ]);
            return;
        }

        $this->status = self::STATUS_SECURITY;
        $this->supportEnds = $latestMajor->getDate()->add($interval);
        $this->normalizeSupportVersions([
            $this->repo->getMostRecentMinorRelease()->getName(),
            $previousMinor->getName(),
        ]);
    }

    /**
     * @throws InvalidArgumentException if any given skeleton is not a
     *     SkeletonPackage instance.
     * @throws InvalidArgumentException if the package repository is not found
     *     in any given skeleton instance.
     */
    private function validateSkeletons(array $skeletons)
    {
        array_walk($skeletons, function ($skeleton) {
            if (! $skeleton instanceof SkeletonPackage) {
                throw new InvalidArgumentException(sprintf(
                    'A value provided in the $skeletons array was invalid; all values must be a %s',
                    SkeletonPackage::class
                ));
            }

            if (! $skeleton->isPackageInSkeleton($this->repo)) {
                throw new InvalidArgumentException(sprintf(
                    'The skeleton "%s" provided in the $skeletons array does not'
                    .' contain "%s" as a requirement; aborting',
                    $skeleton->getName(),
                    $this->repo->getName()
                ));
            }
        });
    }
}
