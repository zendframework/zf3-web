<?php

namespace Release;

use DateTime;

class Release
{
    public $author;
    public $content;
    public $date;
    public $repository;
    public $title;
    public $url;
    public $version;

    public function __construct(
        string $repository,
        string $version,
        string $title,
        string $url,
        string $content,
        DateTime $date,
        Author $author
    ) {
        $this->repository = $repository;
        $this->version = $version;
        $this->title = $title;
        $this->url = $url;
        $this->content = $content;
        $this->date = $date;
        $this->author = $author;
    }
}
