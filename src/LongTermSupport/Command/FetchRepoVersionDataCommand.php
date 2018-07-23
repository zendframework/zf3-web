<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

use Github\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FetchRepoVersionDataCommand extends Command
{
    private const HELP = <<< 'EOT'
This command queries the GitHub API to get a list of all packages, as
well as the tags associated with each. The raw data is then returned to
STDOUT as a PHP file that can then be called via "include()" to provide
to the CachedConfigRepository, allowing you to cache it for the purpose
of testing the lts:build command.
EOT;

    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
        return parent::__construct();
    }

    protected function configure() : void
    {
        $this->setName('lts:fetch-tag-data');
        $this->setDescription('Fetch tag data for all components from GitHub.');
        $this->setHelp(self::HELP);
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $repos = (new GraphqlQuery())->execute($this->client);
        $output->writeLn(sprintf(
            "<?php\nreturn %s;",
            var_export($repos, true)
        ));
        return 0;
    }
}
