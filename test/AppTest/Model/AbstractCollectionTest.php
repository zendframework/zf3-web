<?php
namespace AppTest\Model;

use Mni\FrontYAML\Bridge\CommonMark\CommonMarkParser;
use Mni\FrontYAML\Parser;
use AppTest\Model\TestAsset\Collection;

class AbstractCollectionTest extends \PHPUnit_Framework_TestCase
{
    protected $collection;

    public function setUp()
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
