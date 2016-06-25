<?php
namespace App\Model;


class Changelog extends AbstractCollection
{
    const FOLDER_COLLECTION = 'data/changelog';
    const CACHE_FILE        = 'data/cache/changelog.php';

    protected function order($a, $b)
    {
        return ($a <=> $b) * -1; // reverse order
    }
}
