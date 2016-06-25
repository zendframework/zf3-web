<?php
namespace App\Model;

class Post extends AbstractCollection
{
    const FOLDER_COLLECTION = 'data/posts';
    const CACHE_FILE        = 'data/cache/posts.php';

    protected function order($a, $b)
    {
        return ($a['date'] <=> $b['date']) * -1; // reverse order
    }
}
