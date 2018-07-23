<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

use Github\Client;
use RuntimeException;

class GraphqlQuery
{
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

    public function execute(Client $client) : iterable
    {
        $data = [];

        foreach (self::GRAPHQL_ORGANIZATIONS as $organization) {
            $cursor = self::GRAPHQL_CURSOR_START;

            do {
                $results = $client->api('graphql')->execute(self::GRAPHQL_QUERY, [
                    'organization' => $organization,
                    'cursor' => $cursor,
                ]);

                if (isset($results['errors'])) {
                    throw new RuntimeException(sprintf(
                        'Error fetching tags: %s',
                        $results['errors']['message']
                    ));
                }

                $data = array_merge($data, $results['data']['organization']['repositories']['nodes']);

                $cursor = $this->getNextCursorFromResults($results);
            } while ($cursor);
        }

        return $data;
    }

    private function getNextCursorFromResults(array $results) : ?string
    {
        // @codingStandardsIgnoreStart
        return isset($results['data']['organization']['repositories']['pageInfo']['hasNextPage'])
            ? $results['data']['organization']['repositories']['pageInfo']['endCursor']
            : null;
        // @codingStandardsIgnoreEnd
    }
}
