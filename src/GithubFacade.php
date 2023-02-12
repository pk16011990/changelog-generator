<?php

declare(strict_types=1);

namespace App;

use Github\Client;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class GithubFacade
{
    public function __construct(
        private readonly Client $githubClient,
        private readonly OutputInterface $output,
    )
    {
    }

    /**
     * @param \App\Commit[] $pullRequestsCommits
     * @return \App\GithubIssue[]
     */
    public function getGithubIssuesByPullRequestsCommits(array $pullRequestsCommits, string $githubOwner, string $githubRepositoryName): array
    {
        $progressBar = new ProgressBar($this->output, count($pullRequestsCommits));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');

        $githubIssues = [];
        foreach ($pullRequestsCommits as $pullRequestCommit) {
            $githubIssues[] = $this->createGithubIssue($pullRequestCommit, $githubOwner, $githubRepositoryName);
            $progressBar->advance();
        }
        $progressBar->finish();

        return $githubIssues;
    }

    private function createGithubIssue(Commit $pullRequest, string $githubOwner, string $githubRepositoryName): GithubIssue
    {
        $githubIssueData = $this->githubClient->issue()->show($githubOwner, $githubRepositoryName, $pullRequest->issueNumber);

        return new GithubIssue(
            $githubIssueData['html_url'],
            $pullRequest->issueNumber,
            $githubIssueData['title'],
            $githubIssueData['user']['login'],
            $githubIssueData['user']['html_url'],
            array_map(fn(array $labelData) => $labelData['name'], $githubIssueData['labels']),
        );
    }
}
