<?php

namespace App\Model;

use RuntimeException;

class Issue extends AbstractCollection
{
    const FOLDER_COLLECTION = 'data/issues';
    const CACHE_FILE        = 'data/cache/issues.php';

    protected function buildCache()
    {
        if (empty(static::FOLDER_COLLECTION)) {
            throw new RuntimeException('The folder issues is not defined!');
        }

        if (file_exists(self::CACHE_FILE)) {
            // Nothing to do!
            return;
        }

        $this->collection = ['ZF1' => [], 'ZF2' => []];
        // ZF1 issues
        foreach (glob(static::FOLDER_COLLECTION . '/ZF-*.md') as $file) {
            $doc = $this->yamlParser->parse(file_get_contents($file));
            $fields = $doc->getYAML();
            $this->collection['ZF1'][$file] = $fields;
        }
        // ZF2 issues
        foreach (glob(static::FOLDER_COLLECTION . '/ZF2-*.md') as $file) {
            $doc = $this->yamlParser->parse(file_get_contents($file));
            $fields = $doc->getYAML();
            $this->collection['ZF2'][$file] = $fields;
        }
        file_put_contents(static::CACHE_FILE, '<?php return ' . var_export($this->collection, true) . ';', LOCK_EX);
    }
}
