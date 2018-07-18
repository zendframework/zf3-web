<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

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
        $skeletons = $this->fetchSkeletonPackages();

        $this->packages = array_merge(
            $skeletons,
            array_map(
                function ($repo) use ($skeletons) {
                    $matchedSkeletons = array_reduce(
                        $skeletons,
                        function (array $matched, SkeletonPackage $skeleton) use ($repo) {
                            if ($skeleton->supportEnds() < date('Y-m-d')) {
                                return $matched;
                            }

                            if (! $skeleton->isPackageInSkeleton($repo)) {
                                return $matched;
                            }
                            array_push($matched, $skeleton);
                            return $matched;
                        },
                        []
                    );

                    return new Package($repo, $matchedSkeletons);
                },
                array_filter($repos, function (GithubRepo $repo) {
                    if (in_array($repo->getName(), PackageList::SKELETONS, true)) {
                        return false;
                    }

                    if (version_compare($repo->getMostRecentMinorRelease()->getName(), '1.0.0', 'lt')) {
                        return false;
                    }

                    return true;
                })
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
     * @return SkeletonPackage[]
     */
    private function fetchSkeletonPackages() : array
    {
        if ($this->skeletonPackages) {
            return $this->skeletonPackages;
        }

        $this->skeletonPackages = array_reduce(
            $this->fetchRepoData(),
            function (array $skeletons, GithubRepo $repo) : array {
                if (! in_array($repo->getName(), PackageList::SKELETONS, true)) {
                    return $skeletons;
                }

                $lts = $repo->getMostRecentMinorRelease();

                $composerJsonUrl = sprintf(
                    self::COMPOSER_JSON_URL_TEMPLATE,
                    $repo->getName(),
                    $lts->getRawTagName()
                );

                $response = $this->httpClient->get($composerJsonUrl);
                if (! $response->getStatusCode() === 200) {
                    throw new RuntimeException(sprintf(
                        'Unable to fetch composer.json for skeleton %s, version %s',
                        $repo->getName(),
                        $lts->getName()
                    ));
                }

                $json = (string) $response->getBody();
                $contents = json_decode($json, true);

                array_push($skeletons, new SkeletonPackage($repo, $lts, $contents['require']));
                return $skeletons;
            },
            []
        );

        return $this->skeletonPackages;
    }
}
