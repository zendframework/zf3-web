<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PackageListBuilderCommand extends Command
{
    private const HELP = <<< 'EOT'
Queries the GitHub API to get a list of all packages with stable
releases, along with all tags associated with each. The command then
processes this information to determine whether each package is under
ACTIVE support, SECURITY support (i.e., a new major version has prompted
a support cycle), or LTS support (i.e., the package is a direct
requirement of a skeleton).

The list is then pushed to a configuration file as specified in the
command arguments.
EOT;

    private const HELP_TARGET = <<< 'EOT'
A file to which to write the package support details. The file will be a
JSON file with an entry per package, detailing support status and
supported versions. The command will overwrite any existing file
contents. Typically, use "data/long-term-support.json".
EOT;

    /**
     * @var string
     */
    private $defaultPackageFile;

    /**
     * @var PackageList
     */
    private $packageList;

    public function __construct(PackageList $packageList, string $defaultPackageFile)
    {
        $this->packageList = $packageList;
        $this->defaultPackageFile = $defaultPackageFile;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('lts:build');
        $this->setDescription('Compile a list of support versions for all components.');
        $this->setHelp(self::HELP);
        $this->addArgument('target', InputArgument::OPTIONAL, self::HELP_TARGET);
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $target = $input->getArgument('target') ?? $this->defaultPackageFile;
        if (empty($target)) {
            $output->writeLn(
                '<error>Target MUST be non-empty! Either specify it as an argument,'
                . ' or in the long-term-support.packages-file configuration</error>'
            );
            return 1;
        }

        $output->writeLn('<info>Fetching package details; this may take a while.</info>');
        $packages = $this->packageList->fetchPackages();

        $output->writeLn(sprintf(
            '<info>Writing package details to %s</info>',
            $target
        ));
        file_put_contents($target, json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $output->writeLn('<info>DONE!</info>');

        return 0;
    }
}
