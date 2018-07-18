<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

use DateTimeImmutable;
use DateTimeInterface;
use JsonSerializable;

class Tag implements JsonSerializable
{
    /**
     * @var DateTimeInterface
     */
    private $date;

    /**
     * Short version of tag name, representing the semantic version.
     *
     * @var string
     */
    private $name;

    /**
     * Full tag name as retrieved from github.
     *
     * @var string
     */
    private $tagName;

    public function __construct(string $tagName, string $date)
    {
        $this->tagName = $tagName;
        $this->date = new DateTimeImmutable($date);
        $this->name = preg_replace('#^[^\d]*#', '', $tagName);
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'date' => $this->date->format('Y-m-d'),
            'tag_name' => $this->tagName,
        ];
    }

    public function getDate() : DateTimeInterface
    {
        return $this->date;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getMinorVersion() : string
    {
        $parts = explode('.', $this->name, 3);
        if (3 === count($parts)) {
            array_pop($parts);
        }
        return implode('.', $parts);
    }

    public function getRawTagName() : string
    {
        return $this->tagName;
    }
}
