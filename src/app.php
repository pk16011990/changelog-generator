<?php

declare(strict_types=1);

namespace App;

use Github\AuthMethod;
use Github\Client;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

// ./bin/changelog-cli --repo=/var/www/shopsys --tag=v10.0.0 --github=shopsys/shopsys --github-token=******************** --output=/var/www/changelog-cli/var/changelog.md

// TODO make section configurable
$sections = [
    new Section(':sparkles: Enhancements and features', ['Enhancement']),
    new Section(':hammer: Developer experience and refactoring', ['DX & Refactoring']),
    new Section(':rocket: Performance', ['Performance']),
    new Section(':cloud: Infrastructure', ['Infrastructure']),
    new Section(':art: Design & appearance', ['Design & Appearance']),
    new Section(':bug: Bug Fixes', ['Bug']),
    new Section(':book: Documentation', ['Documentation']),
    new Section(':warning: Security', ['Security']),
];
$defaultSection = new Section(':top: Upgrading', []);

// Initialize all classes

$output = new ConsoleOutput();
$input = new ArgvInput();
$cache = new FilesystemAdapter('github', 0,__DIR__ . '/../var/cache');

$configuration = new Configuration($input, $output);
$configuration->initialize();

$githubClient = new Client();
$githubClient->authenticate($configuration->getGithubToken(), AuthMethod::ACCESS_TOKEN);
$githubClient->addCache($cache);

$githubFacade = new GithubFacade($githubClient, $output);
$gitRepositoryFacade = new GitRepositoryFacade();
$changelogDumper = new ChangelogDumper();

// Generate changelog

$tagPullRequestsCommits = $gitRepositoryFacade->getPullRequestsCommitsBetweenTags(
    $configuration->getRepositoryPath(),
    $configuration->getTag(),
    $configuration->getParentTag(),
);
$githubIssues = $githubFacade->getGithubIssuesByPullRequestsCommits(
    $tagPullRequestsCommits,
    $configuration->getGithubOwner(),
    $configuration->getGithubRepositoryName(),
);
$changelogDumper->dump(
    $githubIssues,
    $sections,
    $defaultSection,
    $configuration->getChangelogOutput(),
    $configuration->getTag(),
    $configuration->getGithubOwner(),
    $configuration->getGithubRepositoryName(),
    $configuration->getParentTag(),
);


