<?php
namespace App\Model;

use Mni\FrontYAML\Parser;

class Changelog
{
    const DIR_POSTS  = 'data/changelog';
    const CACHE_FILE = 'data/cache/changelog.php';

    protected $changelog;

    protected $yamlParser;

    public function __construct(Parser $yamlParser)
    {
        $this->yamlParser = $yamlParser;

        if (!file_exists(self::CACHE_FILE)) {
          $this->buildCache();
        } else {
          $this->changelog = require self::CACHE_FILE;
        }
    }

    public function getAllChangelog()
    {
        return $this->changelog;
    }

    public function getChangelogFromFile($file)
    {
        $result = [];
        if (file_exists($file)) {
          $doc            = $this->yamlParser->parse(file_get_contents($file));
          $result         = $doc->getYAML();
          $result['body'] = $doc->getContent();
        }
        return $result;
    }

    protected function buildCache()
    {
        foreach (glob(self::DIR_POSTS . '/*.md') as $file) {
          $doc = $this->yamlParser->parse(file_get_contents($file));
          $fields = $doc->getYAML();
          $this->changelog[$file] = $fields;
        }
        $this->changelog = array_reverse($this->changelog, true);
        file_put_contents(self::CACHE_FILE, '<?php return ' . var_export($this->changelog, true) . ';', LOCK_EX);
    }
}
