<?php

namespace App\Model;

class Advisory extends AbstractCollection
{
    const FOLDER_COLLECTION = 'data/advisories';
    const CACHE_FILE        = 'data/cache/advisories.php';

    protected function order($a, $b)
    {
        return ($a['date'] <=> $b['date']) * -1; // reverse order
    }
}
