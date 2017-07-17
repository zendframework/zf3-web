<?php

namespace Release;

class Author
{
    public $name;
    public $uri;

    public function __construct(
        string $name,
        string $uri
    ) {
        $this->name = $name;
        $this->uri = $uri;
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'uri'  => $this->uri,
        ];
    }
}
