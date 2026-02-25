<?php

declare(strict_types=1);

namespace Shopsys\MakerBundle\Utils;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Question\Question;

class EntityChoiceHelper
{
    /**
     * @param array<string, string> $entityExtensionMap
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly array $entityExtensionMap,
    ) {
    }

    public function askForEntity(ConsoleStyle $io, string $questionText): string
    {
        while (true) {
            $question = new Question($questionText);
            $question->setValidator(Validator::notBlank(...));

            $question->setAutocompleterValues($this->getAllAvailableEntitiesChoices());

            $answeredEntityClass = $this->convertAutocompleteFormatToFqcn($io->askQuestion($question));

            if (class_exists($answeredEntityClass)) {
                return $answeredEntityClass;
            }

            $io->error(sprintf('Unknown class "%s"', $answeredEntityClass));
        }
    }

    /**
     * @return string[]
     */
    protected function getAllAvailableEntitiesChoices(): array
    {
        $allEntityNames = $this->em->getConfiguration()->getMetadataDriverImpl()?->getAllClassNames() ?? [];
        $allEntityNames = array_combine($allEntityNames, $allEntityNames);

        $availableEntityNames = $this->filterOutShopsysEntitiesThatHaveExtensionInProjectBase($allEntityNames);

        $choices = array_map($this->convertFqcnToAutocompleteFormat(...), $availableEntityNames);
        sort($choices);

        return $choices;
    }

    /**
     * Converts a fully qualified class name to a format suitable for autocompletion.
     * E.g. "Shopsys\FrameworkBundle\Model\Store\Store" -> "Store (Shopsys\FrameworkBundle\Model\Store\Store)"
     */
    protected function convertFqcnToAutocompleteFormat(string $fqcn): string
    {
        $className = Str::getShortClassName($fqcn);

        return sprintf('%s (%s)', $className, $fqcn);
    }

    protected function convertAutocompleteFormatToFqcn(string $autocompleteFormat): string
    {
        if (preg_match('/\((.*?)\)$/', $autocompleteFormat, $matches)) {
            return $matches[1];
        }

        return $autocompleteFormat;
    }

    /**
     * We do not want to offer the base entities that have theirs extension in project base
     * E.g. when there is a Product entity in project-base that extends Shopsys\FrameworkBundle\Model\Product\Product entity, only the extended one is available in the selection
     *
     * @param string[] $allEntityNames
     * @return string[]
     */
    protected function filterOutShopsysEntitiesThatHaveExtensionInProjectBase(array $allEntityNames): array
    {
        return $this->entityExtensionMap
            |> array_keys(...)
            |> array_flip(...)
            |> (fn ($v) => array_diff_key($allEntityNames, $v))
            |> array_values(...);
    }
}
