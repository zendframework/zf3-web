<?php
namespace App\Model;

use Mni\FrontYAML\Parser;

class Post
{
    const DIR_POSTS  = 'data/posts';
    const CACHE_FILE = 'data/cache/posts.php';

    protected $posts;

    protected $yamlParser;

    public function __construct(Parser $yamlParser)
    {
        $this->yamlParser = $yamlParser;

        if (!file_exists(self::CACHE_FILE)) {
          $this->buildCache();
        } else {
          $this->posts = require self::CACHE_FILE;
        }
    }

    public function getAllPosts()
    {
        return $this->posts;
    }

    public function getPostFromFile($file)
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
          $fields['excerpt'] =  substr(strip_tags($doc->getContent()), 0, 256);
          $this->posts[$file] = $fields;
        }
        uasort($this->posts, function($a, $b){
          return ($a['date'] <=> $b['date']) * -1; // reverse order
        });
        file_put_contents(self::CACHE_FILE, '<?php return ' . var_export($this->posts, true) . ';', LOCK_EX);
    }
}
