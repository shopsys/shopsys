<?php

declare(strict_types=1);

namespace Shopsys\Releaser\GithubActions;

use RuntimeException;
use Shopsys\Releaser\Command\SymfonyStyleFactory;
use Symfony\Component\Console\Question\Question;

final class GithubTokenProvider
{
    private ?string $token = null;

    public function __construct(
        private readonly SymfonyStyleFactory $symfonyStyleFactory,
    ) {
    }

    /**
     * Called by ReleaseCommand once at startup with the value of the --github-token CLI option.
     * Pass null when the option was not provided so the provider falls back to the interactive
     * prompt on first getToken() call.
     */
    public function setTokenFromOption(?string $optionValue): void
    {
        if ($optionValue === null || trim($optionValue) === '') {
            return;
        }

        $this->token = $optionValue;
    }

    public function getToken(): string
    {
        if ($this->token !== null) {
            return $this->token;
        }

        $symfonyStyle = $this->symfonyStyleFactory->getPreviouslyCreatedSymfonyStyle();
        $symfonyStyle->note('--github-token was not provided; falling back to interactive prompt.');

        $question = new Question(
            'Please enter no-scope GitHub token (https://github.com/settings/tokens/new)',
        );
        $question->setValidator(static function ($answer): string {
            if (!is_string($answer) || trim($answer) === '') {
                throw new RuntimeException('GitHub token must not be empty');
            }

            return $answer;
        });

        $this->token = $symfonyStyle->askQuestion($question);

        return $this->token;
    }
}
