<?php
namespace App\Model;

use Mni\FrontYAML\Parser;

class Advisory
{
    const DIR_POSTS  = 'data/advisories';
    const CACHE_FILE = 'data/cache/advisories.php';

    protected $advisories;

    protected $yamlParser;

    public function __construct(Parser $yamlParser)
    {
        $this->yamlParser = $yamlParser;

        if (!file_exists(self::CACHE_FILE)) {
          $this->buildCache();
        } else {
          $this->advisories = require self::CACHE_FILE;
        }
    }

    public function getAllAdvisories()
    {
        return $this->advisories;
    }

    public function getAdvisoryFromFile($file)
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
          $fields['id'] = basename($file, '.md');
          $this->advisories[$file] = $fields;
        }
        uasort($this->advisories, function($a, $b){
          return ($a['date'] <=> $b['date']) * -1; // reverse order
        });
        file_put_contents(self::CACHE_FILE, '<?php return ' . var_export($this->advisories, true) . ';', LOCK_EX);
    }
}
