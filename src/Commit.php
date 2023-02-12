<?php

declare(strict_types=1);

namespace App;

class Commit
{
    public function __construct(
        public readonly string $sha,
        public readonly string $message,
        public readonly ?string $issueNumber,
    )
    {
    }
}
