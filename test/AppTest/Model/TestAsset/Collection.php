<?php
namespace AppTest\Model\TestAsset;

use App\Model\AbstractCollection;

class Collection extends AbstractCollection
{
    const FOLDER_COLLECTION = __DIR__ . '/data';
    const CACHE_FILE        = __DIR__ . '/cache.php';
}
