<?php

namespace Release;

use ArrayIterator;
use IteratorAggregate;

class Releases implements IteratorAggregate
{
    private $releases = [];

    public function getIterator()
    {
        $releases = $this->sort($this->releases);
        $releases = $this->truncate($releases);
        return new ArrayIterator($releases);
    }

    public function push(Release $release)
    {
        $this->releases[] = $release;
    }

    private function sort(array $releases) : array
    {
        usort($releases, function (Release $a, Release $b) {
            return $a->date <=> $b->date;
        });
        return array_reverse($releases);
    }

    private function truncate(array $releases) : array
    {
        if (count($releases) < 15) {
            return $releases;
        }

        return array_slice($releases, 0, 15);
    }
}
