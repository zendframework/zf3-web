<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

use Github\Client;
use RuntimeException;

class GraphqlRepository implements RepositoryInterface
{
    use GraphqlDataProcessingTrait;

    /**
     * Arbitrary start cursor for paginated results.
     *
     * @var string
     */
    private const GRAPHQL_CURSOR_START = 'Y3Vyc29yOjEwMA==';

    /**
     * Organizations we will query.
     *
     * @var string[]
     */
    private const GRAPHQL_ORGANIZATIONS = [
        'zendframework',
        'zfcampus',
    ];

    /**
     * Query for fetching all tag data by repository from GitHub.
     *
     * The two ids provided are the GitHub unique identifiers for the zendframework
     * and zfcampus organizations, respectively.
     *
     * The "after" string is for providing an initial cursoer so that we can page
     * through results in order to retrieve all information.
     *
     * @var string
     */
    private const GRAPHQL_QUERY = <<< 'EOT'
query tagsByOrganization(
  $organization: String!
  $cursor: String!
) {
  organization(login: $organization) {
    repositories(first: 100, after: $cursor) {
      pageInfo {
        startCursor
        hasNextPage
        endCursor
      }
      nodes {
        nameWithOwner
        tags: refs(refPrefix: "refs/tags/", first: 100, orderBy: {field: TAG_COMMIT_DATE, direction: DESC}) {
          edges {
            tag: node {
              name
              target {
                ... on Commit {
                  pushedDate
                }
                ... on Tag {
                  tagger {
                    date
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}
EOT;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var GithubRepo[]
     */
    private $data = [];

    public function __construct(Client $client, array $filterCriteria)
    {
        $this->client = $client;
        $this->assignCriteriaCallbacks($filterCriteria);
    }

    public function getAllRepos() : iterable
    {
        if ([] !== $this->data) {
            return $this->data;
        }

        $this->data = $this->processData((new GraphqlQuery())->execute($this->client));

        return $this->data;
    }
}
