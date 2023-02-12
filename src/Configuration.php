<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;

class Configuration
{


    private readonly InputDefinition $inputDefinition;
    private ?array $options = null;
    public function __construct(
        private readonly InputInterface $input,
        private readonly OutputInterface $output,
    ) {
    }

    private function initializeDefinition(): void
    {
        $this->inputDefinition = new InputDefinition([
            new InputOption('repo', 'r', InputOption::VALUE_OPTIONAL, 'Path to local git repository'),
            new InputOption('tag', 't', InputOption::VALUE_OPTIONAL, 'Git tag for which the changelog has been generated'),
            new InputOption('github', 'g', InputOption::VALUE_OPTIONAL, 'Github repo {owner}/{repoName}'),
            new InputOption('github-token', 'T', InputOption::VALUE_OPTIONAL, 'Github token - https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/creating-a-personal-access-token'),
            new InputOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Path to output file'),
        ]);
    }

    public function getRepositoryPath(): string
    {
        return $this->getOption('repo');
    }

    public function getTag(): string
    {
        return $this->getOption('tag');
    }

    public function getParentTag(): string
    {
        return $this->getOption('parent-tag');
    }

    public function getGithubOwner(): string
    {
        return $this->getOption('github-owner');
    }

    public function getGithubRepositoryName(): string
    {
        return $this->getOption('github-repo');
    }

    public function getGithubToken(): string
    {
        return $this->getOption('github-token');
    }

    public function getChangelogOutput(): string
    {
        return $this->getOption('output');
    }

    public function initialize(): void
    {
        if ($this->options !== null) {
            throw new \RuntimeException('Configuration is already initialized.');
        }

        $this->initializeDefinition();
        $this->bindInput();

        $userOptions = $this->input->getOptions();

        foreach ($this->inputDefinition->getOptions() as $inputOption) {
            if ($userOptions[$inputOption->getName()] === null) {
                $userOptions[$inputOption->getName()] = $this->getOptionFromUser($inputOption);
            }
        }

        $this->options = $this->setAdditionalOptions($userOptions);
    }

    private function bindInput(): void
    {
        try {
            $this->input->bind($this->inputDefinition);
        } catch (RuntimeException $exception) {
            if ($exception->getMessage() === 'The "--help" option does not exist.') {
                $helper = new DescriptorHelper();
                $helper->describe($this->output, $this->inputDefinition);
                die();
            }
        }
    }

    private function getOptionFromUser(InputOption $inputOption): string
    {
        $questionHelper = new QuestionHelper();
        $question = new Question($inputOption->getDescription() . ': ');
        $question->setMaxAttempts(null);
        $question->setValidator(function ($answer): string {
            if (!is_string($answer)) {
                throw new \RuntimeException('This is required!');
            }

            return $answer;
        });
        switch ($inputOption->getName()) {
            case 'github-token':
                $question->setHidden(true);
                $question->setHiddenFallback(false);
                break;
            case 'github':
                $question->setValidator(function ($answer): string {
                    if (!is_string($answer)) {
                        throw new \RuntimeException('This is required!');
                    }
                    if (count(explode('/', $answer)) !== 2) {
                        throw new \RuntimeException('Format is `repoOwner/repoName` from github url like `https://github.com/repoOwner/repoName`');
                    }

                    return $answer;
                });
                break;
            default:
        }

        return $questionHelper->ask($this->input, $this->output, $question);
    }

    private function getOption(string $optionName): string
    {
        if ($this->options === null || !isset($this->options[$optionName])) {
            throw new \RuntimeException('The configuration is not loaded correctly.');
        }

        return $this->options[$optionName];
    }

    private function setAdditionalOptions(array $userOptions): array
    {
        $githubParts = explode('/', $userOptions['github']);
        if (count($githubParts) !== 2) {
            throw new \RuntimeException('Option `github` must have format `repoOwner/repoName`');
        }
        $userOptions['github-owner'] = $githubParts[0];
        $userOptions['github-repo'] = $githubParts[1];

        $process = new Process(['git', '-C', $userOptions['repo'], 'describe', '--abbrev=0', '--tags', $userOptions['tag'] . '^']);
        $process->run();
        $userOptions['parent-tag'] = trim($process->getOutput());

        return $userOptions;
    }
}
