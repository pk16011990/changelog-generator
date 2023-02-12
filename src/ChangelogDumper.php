<?php

declare(strict_types=1);

namespace App;

class ChangelogDumper
{
    /**
     * @param \App\GithubIssue[] $githubIssues
     * @param \App\Section[] $sections
     */
    public function dump(
        array $githubIssues,
        array $sections,
        Section $defaultSection,
        string $outputFilepath,
        string $tag,
        string $githubOwner,
        string $githubRepositoryName,
        string $parentTag
    ): void
    {
        $changelog = $this->getHeaderDump($tag, $githubOwner, $githubRepositoryName, $parentTag);

        $previousRenderedSection = null;
        foreach ($sections as $section) {
            foreach ($githubIssues as $githubIssueKey => $githubIssue) {
                if ($section->isInSection($githubIssue)) {
                    if ($previousRenderedSection !== $section) {
                        $changelog .= $this->getSectionDump($section);
                        $previousRenderedSection = $section;
                    }

                    $changelog .= $this->getGithubIssueDump($githubIssue);

                    unset($githubIssues[$githubIssueKey]);
                }
            }
        }

        if (count($githubIssues) > 0) {
            $changelog .= $this->getSectionDump($defaultSection);

            foreach ($githubIssues as $githubIssue) {
                $changelog .= $this->getGithubIssueDump($githubIssue);
            }
        }

        file_put_contents($outputFilepath, $changelog);
    }

    private function getHeaderDump(string $tag, string $githubOwner, string $githubRepositoryName, string $parentTag): string
    {
        return sprintf(
            '## [%s](https://github.com/%s/%s/compare/%s...%s) (%s)',
            $tag,
            $githubOwner,
            $githubRepositoryName,
            $parentTag,
            $tag,
            date('Y-m-d')
        );
    }

    public function getGithubIssueDump(GithubIssue $githubIssue): string
    {
        return sprintf(
            PHP_EOL . '- %s  [\#%s](%s) ([%s](%s))',
            MarkdownHelper::escapeMarkdown($githubIssue->title),
            $githubIssue->number,
            $githubIssue->url,
            MarkdownHelper::escapeMarkdown($githubIssue->authorName),
            $githubIssue->authorUrl,
        );
    }

    /**
     * @param \App\Section $defaultSection
     * @return string
     */
    public function getSectionDump(Section $defaultSection): string
    {
        return PHP_EOL . PHP_EOL . $defaultSection->headline . PHP_EOL;
    }
}
