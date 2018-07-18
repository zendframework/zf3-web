<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

use InvalidArgumentException;
use RuntimeException;

trait GraphqlDataProcessingTrait
{
    /**
     * @var callable[]
     */
    private $criteria;

    private function assignCriteriaCallbacks(array $filterCriteria) : void
    {
        $this->criteria = array_map(function ($criteria) {
            if (! is_callable($criteria)) {
                throw new InvalidArgumentException(sprintf(
                    'One or more filter criteria to %s is non-callable',
                    __CLASS__
                ));
            }

            return function (string $repoName) use ($criteria) : bool {
                return $criteria($repoName);
            };
        }, $filterCriteria);
    }

    /**
     * @return GithubRepo[]
     */
    private function processData(array $data) : iterable
    {
        return array_map(
            function ($repository) {
                return new GithubRepo(
                    $repository['nameWithOwner'],
                    isset($repository['tags']['edges'])
                        ? $this->getTags(array_column($repository['tags']['edges'], 'tag'))
                        : []
                );
            },
            array_filter($data, function ($repository) {
                foreach ($this->criteria as $criteria) {
                    if (! $criteria($repository['nameWithOwner'])) {
                        return false;
                    }
                }
                return true;
            })
        );
    }

    /**
     * @return Tag[]
     */
    private function getTags(array $allTagData) : array
    {
        return array_map(
            function ($tagData) {
                return new Tag(
                    $tagData['name'],
                    $tagData['target']['pushedDate'] ?? $tagData['target']['tagger']['date']
                );
            },
            array_filter($allTagData, function ($tagData) {
                // Filter out invalid tag names, such as zf/release-2.0.0beta5
                if (! preg_match(
                    '/^(release-|v\.?)?\d+\.\d+\.\d+([a-z]+[a-z0-9])*$/',
                    $tagData['name']
                )) {
                    return false;
                }

                // Filter out any that do not have essential tag date information
                return isset($tagData['target']['pushedDate'])
                    || isset($tagData['target']['tagger']['date']);
            })
        );
    }

    /**
     * @throws RuntimeException
     */
    private function throwExceptionFromResults(array $results)
    {
        if (! isset($results['errors'])) {
            throw new RuntimeException(
                'Unexpected payload returned, and does not indicate errors; aborting'
            );
        }

        throw new RuntimeException($results['errors']['message']);
    }
}
