<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

use Closure;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Http\Client\Common\HttpMethodsClient;
use RuntimeException;

/**
 * Entry point for retrieving package support details.
 */
class PackageList
{
    /**
     * Skeleton packages with LTS constraints.
     *
     * @var string[]
     */
    public const SKELETONS = [
        'zendframework/ZendSkeletonApplication',
        'zendframework/zend-expressive-skeleton',
        'zfcampus/zf-apigility-skeleton',
    ];

    /**
     * Template for retrieving the composer.json of a release package for a
     * repository.
     *
     * @var string
     */
    private const COMPOSER_JSON_URL_TEMPLATE = 'https://raw.githubusercontent.com/%s/%s/composer.json';

    /**
     * @var HttpMethodsClient
     */
    private $httpClient;

    /**
     * @var ?Package[]
     */
    private $packages;

    /**
     * Normalized data containing all package repositories and related tags.
     *
     * @var ?array
     */
    private $repoData;

    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var ?SkeletonPackage[]
     */
    private $skeletonPackages;

    public function __construct(RepositoryInterface $repository, HttpMethodsClient $httpClient)
    {
        $this->repository = $repository;
        $this->httpClient = $httpClient;
    }

    /**
     * @return Package[]
     */
    public function fetchPackages() : array
    {
        if ($this->packages) {
            return $this->packages;
        }

        $repos = $this->fetchRepoData();
        $today = new DateTimeImmutable('now');
        $skeletons = $this->fetchSkeletonPackages($repos, $today);

        $this->packages = array_merge(
            $skeletons,
            array_reduce(
                // Filter out any pre-1.0.0 or skeleton repos from the repo list
                array_filter($repos, Closure::fromCallable([$this, 'repoFilter'])),
                // And then build packages from those repos
                $this->generatePackageReducer($skeletons, $today),
                []
            )
        );

        return $this->packages;
    }

    /**
     * @return array Array of repository data. Each item in the array consists of:
     *     - nameWithOwner: string name in org/repo format
     *     - tags: array of tag data
     */
    private function fetchRepoData() : array
    {
        return $this->repoData ?: $this->repository->getAllRepos();
    }

    /**
     * @return Package[] Array will contain both Package and SkeletonPackage
     *     instances. SkeletonPackage instances indicate LTS versions, while
     *     Package instances are active support versions. The array will
     *     contain N + 1 instances per skeleton listed: an active support
     *     Package instance ALWAYS, and POTENTIALLY 1 or 2 SkeletonPackage
     *     instances (one for current major, and one for previous minor,
     *     depending on whether or not LTS has expired).
     */
    private function fetchSkeletonPackages(array $repos, DateTimeInterface $today) : array
    {
        if ($this->skeletonPackages) {
            return $this->skeletonPackages;
        }

        $this->skeletonPackages = array_reduce(
            $repos,
            $this->generateSkeletonReducer($today),
            []
        );

        return $this->skeletonPackages;
    }

    private function generateSkeletonReducer(DateTimeInterface $today) : callable
    {
        return function (array $skeletons, GithubRepo $repo) use ($today) : array {
            if (! in_array($repo->getName(), PackageList::SKELETONS, true)) {
                return $skeletons;
            }

            // We always have an active support version
            array_push($skeletons, new Package($repo));

            // Get the most recent major release version
            $major = $repo->getMostRecentMajorRelease();

            if (! $major) {
                // No major releases yet
                return $skeletons;
            }

            $majorExpires = $major->getDate()->add(new DateInterval('P4Y'));
            if ($majorExpires < $today) {
                // LTS has expired; we're done
                return $skeletons;
            }

            // Add LTS for current major version
            array_push($skeletons, $this->createLtsSkeletonPackage(
                $repo,
                $major,
                $majorExpires
            ));

            if (version_compare('1.0.0', $major->getName(), 'gt')) {
                // We're done; no LTS support for 0.Y versions.
                return $skeletons;
            }

            // Previous LTS for MVC skeleton was 2.4; do not report 2.5 as an
            // LTS version
            if ('zendframework/ZendSkeletonApplication' === $repo->getName()
                && version_compare('3.0.0', $major->getName(), 'ge')
            ) {
                return $skeletons;
            }

            $minor = $repo->getPreviousMinorRelease($major);
            $minorExpires = $major->getDate()->add(new DateInterval('P2Y'));
            if ($minorExpires < $today) {
                // LTS has expired for the previous mionr release; we're done
                return $skeletons;
            }

            // Add LTS for previous minor version
            array_push($skeletons, $this->createLtsSkeletonPackage(
                $repo,
                $minor,
                $minorExpires
            ));

            return $skeletons;
        };
    }

    private function createLtsSkeletonPackage(
        GithubRepo $repo,
        Tag $ltsTag,
        DateTimeInterface $ltsExpires
    ) : SkeletonPackage {
        $composerJsonUrl = sprintf(
            self::COMPOSER_JSON_URL_TEMPLATE,
            $repo->getName(),
            $ltsTag->getRawTagName()
        );

        $response = $this->httpClient->get($composerJsonUrl);
        if (! $response->getStatusCode() === 200) {
            throw new RuntimeException(sprintf(
                'Unable to fetch composer.json for skeleton %s, version %s',
                $repo->getName(),
                $ltsTag->getName()
            ));
        }

        $json = (string) $response->getBody();
        $contents = json_decode($json, true);

        return new SkeletonPackage($repo, $ltsTag, $ltsExpires, $contents['require']);
    }

    private function repoFilter(GithubRepo $repo) : bool
    {
        if (in_array($repo->getName(), PackageList::SKELETONS, true)) {
            return false;
        }

        if (version_compare(
            $repo->getMostRecentMinorRelease()->getName(),
            '1.0.0',
            'lt'
        )) {
            return false;
        }

        return true;
    }

    private function generatePackageReducer(array $skeletons, DateTimeInterface $today) : callable
    {
        return function ($packages, $repo) use ($skeletons, $today) {
            // We always have an ACTIVE support version
            array_push($packages, new Package($repo));

            // Check for skeletons that contain the package, and create
            // an LTS version for each.
            $packages = array_merge(
                $packages,
                array_reduce(
                    array_filter($skeletons, $this->generateSkeletonFilter($repo)),
                    function (array $packages, SkeletonPackage $skeleton) use ($repo) : array {
                        array_push($packages, new LtsPackage($repo, $skeleton));
                        return $packages;
                    },
                    []
                )
            );

            $major = $repo->getMostRecentMajorRelease();
            if (! $major || version_compare('1.0.0', $major->getName(), 'ge')) {
                // No possible security support releases for 1.0 packages; done.
                return $packages;
            }

            $majorExpires = $major->getDate()->add(new DateInterval('P1Y'));
            if ($majorExpires < $today) {
                // Security release is done; nothing to add.
                return $packages;
            }

            // Security release is in effect; add it.
            $minor = $repo->getPreviousMinorRelease($major);
            array_push($packages, new SecurityPackage($repo, $minor, $majorExpires));

            return $packages;
        };
    }

    private function generateSkeletonFilter(GithubRepo $repo) : callable
    {
        return function (Package $skeleton) use ($repo) : bool {
            if (! $skeleton instanceof SkeletonPackage) {
                // Skip ACTIVE support skeleton packages
                return false;
            }

            if (! $skeleton->isPackageInSkeleton($repo)) {
                // Skip if package is not a skeleton requirement
                return false;
            }

            return true;
        };
    }
}
