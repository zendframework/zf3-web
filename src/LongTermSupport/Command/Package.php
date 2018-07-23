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
     * @var ?string
     */
    protected $skeleton;

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

    public function __construct(GithubRepo $repo)
    {
        $this->repo = $repo;

        $tags = $repo->getTags();
        $latest = array_shift($tags);
        $this->normalizeSupportVersions([$latest->getName()]);
    }

    public function jsonSerialize()
    {
        $supportEnds = $this->supportEnds();
        return [
            'name' => $this->getName(),
            'skeleton' => $this->getSkeleton() ?: '',
            'status' => $this->status,
            'support_ends' => $supportEnds ? $supportEnds->format('Y-m-d') : 'N/A',
            'url'  => $this->getUrl(),
            'versions' => $this->getVersions(),
        ];
    }

    public function getSkeleton() : ?string
    {
        return $this->skeleton;
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

    protected function normalizeSupportVersions(array $versions) : void
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
}
