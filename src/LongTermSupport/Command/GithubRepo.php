<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

use JsonSerializable;
use OutOfBoundsException;

class GithubRepo implements JsonSerializable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Tag[]
     */
    private $tags;

    public function __construct(string $name, array $tags)
    {
        $this->name = $name;
        $this->tags = $tags;

        // Sort by version, descending
        usort($this->tags, function ($a, $b) {
            return version_compare($b->getName(), $a->getName());
        });
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'tags' => $this->tags,
        ];
    }

    public function getMostRecentMajorRelease() : ?Tag
    {
        foreach ($this->tags as $tag) {
            if (preg_match('/\.0\.0$/', $tag->getName())) {
                return $tag;
            }
        }

        return null;
    }

    public function getPreviousMinorRelease(Tag $majorRelease) : ?Tag
    {
        $majorReleaseVersion = $majorRelease->getName();
        if ($majorReleaseVersion === '1.0.0') {
            return null;
        }

        foreach ($this->tags as $tag) {
            $tagName = $tag->getName();

            // If the major version is <= tag version, move on
            if (version_compare($majorReleaseVersion, $tagName, 'le')) {
                continue;
            }

            // We now have a version less than the release version; if it ends
            // in .0, we've found the previous minor release.
            if (preg_match('/\.0$/', $tagName)) {
                return $tag;
            }
        }

        return null;
    }

    public function getMostRecentMinorRelease() : Tag
    {
        foreach ($this->tags as $tag) {
            if (preg_match('/\.0$/', $tag->getName())) {
                return $tag;
            }
        }

        throw new OutOfBoundsException(sprintf(
            'Unable to find a minor release of any kind for package %s',
            $this->name
        ));
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return Tag[]
     */
    public function getTags() : array
    {
        return $this->tags;
    }
}
