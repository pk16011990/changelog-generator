<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\Process\Process;

class GitRepositoryFacade
{
    /**
     * @return \App\Commit[]
     */
    public function getPullRequestsCommitsBetweenTags(string $repositoryPath, string $fromTag, string $toTag): array
    {
        $process = new Process(['git', '-C', $repositoryPath, 'log', '--pretty=%h#%s', $fromTag . '...' . $toTag]);
        $process->run();
        $commitMessages = array_filter(explode(PHP_EOL, $process->getOutput()));

        $pullRequestsCommits = [];
        foreach ($commitMessages as $commitMessage) {
            $matches = null;
            if (preg_match('/^(?P<sha>[a-f\d]{10})#(?P<message>.*\(#(?P<id>\d+)\))$/', $commitMessage, $matches) !== 0) {
                $pullRequestsCommits[] = new Commit($matches['sha'], $matches['message'], $matches['id']);
            }
        }

        return $this->filterOnlyTagCommits($pullRequestsCommits, $repositoryPath, $fromTag);
    }

    /**
     * @param \App\Commit[] $commits
     * @return \App\Commit[]
     */
    private function filterOnlyTagCommits(array $commits, string $repositoryPath, string $tag): array
    {
        return array_filter($commits, function (Commit $commit) use ($repositoryPath, $tag): bool {
            $process = new Process(['git', '-C', $repositoryPath, 'describe', '--contains', $commit->sha]);
            $process->run();

            return $tag === explode('~', $process->getOutput())[0];
        });
    }
}
