<?php

declare(strict_types=1);

namespace App;

use function Symfony\Component\String\u;

class Section
{

    public function __construct(
        public readonly string $headline,
        /** @var string[] */
        public readonly array $labels,
    )
    {
    }

    public function isInSection(GithubIssue $issue): bool
    {
        foreach ($issue->labels as $label) {
            if (u($label)->ignoreCase()->equalsTo($this->labels)) {
                return true;
            }
        }

        return false;
    }
}
