<?php

declare(strict_types=1);

namespace App;

class GithubIssue
{
    public function __construct(
        public readonly string $url,
        public readonly string $number,
        public readonly string $title,
        public readonly string $authorName,
        public readonly string $authorUrl,
        /** @var string[] */
        public readonly array $labels,
    )
    {
    }
}
