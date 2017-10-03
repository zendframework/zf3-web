<?php
chdir(dirname(__DIR__));
require 'vendor/autoload.php';

use Github\Client;
use Zend\Config\Writer;

if (! isset($argv[1])) {
    fwrite(STDERR, printf("Usage: php %s <github token>\n", basename(__FILE__)));
    exit(1);
}

$github = new Client();
$github->authenticate($argv[1], null, $github::AUTH_URL_TOKEN);
$org = $github->organization()->show('zendframework');
$reposCount = $org['public_repos'];

$repos = [];
$page = 1;
while (count($repos) < $reposCount) {
    $results = $github->organization()->repositories('zendframework', 'public', $page);

    foreach ($results as $repo) {
        $repos[$repo['name']] = [
            'description' => $repo['description'],
            'docs' => (int) $github->repo()->contents()->exists('zendframework', $repo['name'], 'mkdocs.yml'),
        ];
    }

    ++$page;
}

ksort($repos);

$writer = new Writer\PhpArray();
$writer->setUseBracketArraySyntax(true);

$writer->toFile(
    dirname(__DIR__) . '/config/autoload/zf-components.local.php',
    ['zf_components' => $repos]
);
