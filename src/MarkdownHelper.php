<?php

declare(strict_types=1);

namespace App;

class MarkdownHelper
{
    public static function escapeMarkdown(string $markdown): string
    {
        return str_replace(
            ['\\', '-', '#', '*', '+', '`', '.', '[', ']', '(', ')', '!', '&', '<', '>', '_', '{', '}'],
            ['\\\\', '\-', '\#', '\*', '\+', '\`', '\.', '\[', '\]', '\(', '\)', '\!', '\&', '\<', '\>', '\_', '\{', '\}'],
            $markdown
        );
    }
}
