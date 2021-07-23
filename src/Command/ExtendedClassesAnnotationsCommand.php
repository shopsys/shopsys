<?php

declare(strict_types=1);

namespace App\Command;

use Shopsys\FrameworkBundle\Command\CommandResultCodes;
use Shopsys\FrameworkBundle\Command\ExtendedClassesAnnotationsCommand as BaseExtendedClassesAnnotationsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @property \App\Component\ClassExtension\MethodAnnotationsFactory $methodAnnotationsAdder
 * @method __construct(string $projectRootDirectory, \Shopsys\FrameworkBundle\Component\ClassExtension\ClassExtensionRegistry $classExtensionRegistry, \Shopsys\FrameworkBundle\Component\ClassExtension\PropertyAnnotationsFactory $propertyAnnotationsFactory, \App\Component\ClassExtension\MethodAnnotationsFactory $methodAnnotationsAdder, \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer $annotationsReplacer, \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap $annotationsReplacementsMap, \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsAdder $annotationsAdder)
 */
class ExtendedClassesAnnotationsCommand extends BaseExtendedClassesAnnotationsCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:extended-classes:annotations';

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        error_reporting(error_reporting() & ~E_DEPRECATED);

        // Following logic is workaround for issue https://github.com/shopsys/shopsys/pull/2369
        $symfonyStyle = new SymfonyStyle($input, $output);
        $isDryRun = (bool)$input->getOption(static::DRY_RUN);
        $filesForReplacingAnnotations = $this->replaceFrameworkWithProjectAnnotations($isDryRun);
        if (count($filesForReplacingAnnotations) > 0) {
            if ($isDryRun) {
                $symfonyStyle->error('Following files need fixing annotations:');
                $symfonyStyle->listing($filesForReplacingAnnotations);
            } else {
                $symfonyStyle->note(
                    ['Annotations were fixed in the following files:'] + $filesForReplacingAnnotations
                );
            }
        }
        $filesForAddingPropertyOrMethodAnnotations = $this->addPropertyAndMethodAnnotationsToProjectClasses($isDryRun);

        if (count($this->methodAnnotationsAdder->getWarningBag()) > 0) {
            foreach ($this->methodAnnotationsAdder->getWarningBag() as $exception) {
                $symfonyStyle->warning($exception->getMessage());
            }
        }

        if (count($filesForAddingPropertyOrMethodAnnotations) > 0) {
            if ($isDryRun) {
                $symfonyStyle->error('@method or @property annotations need to be added to the following files:');
                $symfonyStyle->listing($filesForAddingPropertyOrMethodAnnotations);
            } else {
                $symfonyStyle->note(
                    ['@method or @property annotations were added to the following files:'] + $filesForAddingPropertyOrMethodAnnotations
                );
            }
        }
        if (count($filesForReplacingAnnotations) === 0 && count($filesForAddingPropertyOrMethodAnnotations) === 0) {
            $symfonyStyle->success('All good!');
            return CommandResultCodes::RESULT_OK;
        }

        if ($isDryRun) {
            $symfonyStyle->note('You can fix the annotations using "annotations-fix" phing command.');
            return CommandResultCodes::RESULT_FAIL;
        }

        return CommandResultCodes::RESULT_OK;
    }
}
