<?php

namespace AppTest\Model;

use AppTest\Model\TestAsset\Collection;
use Mni\FrontYAML\Bridge\CommonMark\CommonMarkParser;
use Mni\FrontYAML\Parser;
use PHPUnit\Framework\TestCase;

class AbstractCollectionTest extends TestCase
{
    /** @var Collection */
    private $collection;

    protected function setUp()
    {
        $this->collection = new Collection(
            new Parser(null, new CommonMarkParser())
        );
    }

    public function testGetAll()
    {
        $items = $this->collection->getAll();
        $this->assertNotEmpty($items);
        $this->assertCount(1, $items);
    }

    public function testGetFromFile()
    {
        $item = $this->collection->getFromFile(__DIR__ . '/TestAsset/data/item.md');
        $this->assertEquals('layout', $item['layout']);
        $this->assertEquals('title', $item['title']);
        $this->assertEquals(1234828800, $item['date']);
        $this->assertContains('body', $item['body']);
    }
}
