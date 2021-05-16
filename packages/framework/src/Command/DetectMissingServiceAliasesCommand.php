<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Roave\BetterReflection\Reflection\ReflectionClass;
use Shopsys\FrameworkBundle\Component\ServiceAliasContainerInfoRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Yaml\Yaml;

class DetectMissingServiceAliasesCommand extends Command
{
    protected const FQCN_PREFIX_APPLICATION = 'App\\';
    protected const FQCN_PREFIX_EXTENDED_SERVICES = 'Shopsys\\';
    protected const SETTING_YAML_FILENAME = 'detect-missing-service-aliases.yaml';
    protected const SETTING_IGNORED_ALIASES = 'ignoredAliases';

    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:extended-classes:aliases';

    /**
     * @var \Shopsys\FrameworkBundle\Component\ServiceAliasContainerInfoRegistry
     */
    private ServiceAliasContainerInfoRegistry $containerInfoRegistry;

    /**
     * @var string
     */
    private string $settingYamlFilePath;

    /**
     * @param \Shopsys\FrameworkBundle\Component\ServiceAliasContainerInfoRegistry $containerInfoRegistry
     * @param string $shopsysRootDir
     */
    public function __construct(ServiceAliasContainerInfoRegistry $containerInfoRegistry, string $shopsysRootDir)
    {
        parent::__construct();

        $this->containerInfoRegistry = $containerInfoRegistry;
        $this->settingYamlFilePath = realpath($shopsysRootDir) . '/config/' . static::SETTING_YAML_FILENAME;
    }

    protected function configure()
    {
        $this
            ->setDescription(
                'Detect possible missing service aliases of extended Shopsys Framework classes.'
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $symfonyStyleIo = new SymfonyStyle($input, $output);

        $symfonyStyleIo->text('Detecting misconfigurations in Shopsys Framework service aliases...');

        $appServices = $this->getAppServicesWithParentsAndInterfaces();

        $missingDirectAliases = $this->getMissingDirectAliases($appServices);
        if ($missingDirectAliases !== []) {
            $this->printMissingDirectAliasesWarning($symfonyStyleIo, $missingDirectAliases);
        }

        $possibleMissingAliases = $this->getPossibleMissingAliasedServices($appServices);
        $unambiguousMissingAliases = $this->filterUnambiguousPossibleMissingAliasedServices($possibleMissingAliases);
        if ($unambiguousMissingAliases !== []) {
            $this->printUnambiguousPossibleMissingAliasesWarning($symfonyStyleIo, $unambiguousMissingAliases);
        }
        if ($possibleMissingAliases !== []) {
            $this->printAmbiguousPossibleMissingAliasesWarning($symfonyStyleIo, $possibleMissingAliases);
        }

        if ($missingDirectAliases + $unambiguousMissingAliases + $possibleMissingAliases !== []) {
            $symfonyStyleIo->error(sprintf(
                'There were detected some possible misconfigurations of Shopsys Framework service aliases!'
                . ' Please, either update your service definitions or ignore them in "%s".',
                $this->settingYamlFilePath
            ));

            $this->printRecommendation(
                $symfonyStyleIo,
                $missingDirectAliases,
                $unambiguousMissingAliases,
                $possibleMissingAliases
            );

            return CommandResultCodes::RESULT_FAIL;
        }

        $symfonyStyleIo->success('There are no detectable misconfigurations of Shopsys Framework service aliases!');

        return CommandResultCodes::RESULT_OK;
    }

    /**
     * @return array<string,array<string,string|string[]>>
     */
    private function getAppServicesWithParentsAndInterfaces(): array
    {
        $appServicesWithParentsAndInterfaces = [];
        foreach ($this->containerInfoRegistry->getServiceIdsToClassNames() as $serviceId => $className) {
            if (str_starts_with($serviceId, static::FQCN_PREFIX_APPLICATION)) {
                $reflectionClass = ReflectionClass::createFromName($className);
                $appServicesWithParentsAndInterfaces[$serviceId] = [
                    'id' => $serviceId,
                    'parents' => $reflectionClass->getParentClassNames(),
                    'interfaces' => $reflectionClass->getInterfaceNames(),
                ];
            }
        }
        return $appServicesWithParentsAndInterfaces;
    }

    /**
     * @param array<string,array<string,string|string[]>> $appServices
     * @return array<string,string>
     */
    private function getMissingDirectAliases(array $appServices): array
    {
        $missingDirectAliases = [];
        $aliasIdsToAliases = $this->containerInfoRegistry->getAliasIdsToAliases();
        $aliasIdsToAliases = array_diff($aliasIdsToAliases, $this->getIgnoredAliases());
        foreach ($aliasIdsToAliases as $aliasId => $alias) {
            $finalAlias = $alias;
            while (array_key_exists($finalAlias, $aliasIdsToAliases)) {
                $finalAlias = $aliasIdsToAliases[$finalAlias];
            }
            if (array_key_exists($finalAlias, $appServices) && $aliasIdsToAliases[$aliasId] !== $finalAlias) {
                $missingDirectAliases[$aliasId] = $finalAlias;
            }
        }
        ksort($missingDirectAliases);

        return $missingDirectAliases;
    }

    /**
     * @param array<string,array<string,string|string[]>> $appServices
     * @return array<string,string[]>
     */
    private function getPossibleMissingAliasedServices(array $appServices): array
    {
        $possibleMissingAliasedServices = [];
        $serviceClassNames = array_keys($this->containerInfoRegistry->getServiceIdsToClassNames());
        foreach ($appServices as $appService) {
            $possibleServiceIds = array_merge($appService['parents'], $appService['interfaces']);
            $possibleServiceIds = array_diff($possibleServiceIds, $this->getIgnoredAliases());
            foreach ($possibleServiceIds as $possibleServiceId) {
                $serviceExists = in_array($possibleServiceId, $serviceClassNames, true);
                if ($serviceExists && str_starts_with($possibleServiceId, static::FQCN_PREFIX_EXTENDED_SERVICES)) {
                    $possibleMissingAliasedServices[$possibleServiceId][] = $appService['id'];
                }
            }
        }
        ksort($possibleMissingAliasedServices);

        return $possibleMissingAliasedServices;
    }

    /**
     * @param array<string,string[]> $possibleMissingAliasedServices
     * @return array<string,string>
     */
    private function filterUnambiguousPossibleMissingAliasedServices(array &$possibleMissingAliasedServices): array
    {
        $unambiguousPossibleMissingAliasedServices = [];
        foreach ($possibleMissingAliasedServices as $possibleMissingAliasId => $serviceIds) {
            if (count($serviceIds) === 1) {
                $unambiguousPossibleMissingAliasedServices[$possibleMissingAliasId] = $serviceIds[0];
                unset($possibleMissingAliasedServices[$possibleMissingAliasId]);
            }
        }

        return $unambiguousPossibleMissingAliasedServices;
    }

    /**
     * @return string[]
     */
    private function getIgnoredAliases(): array
    {
        if (file_exists($this->settingYamlFilePath)) {
            return Yaml::parseFile($this->settingYamlFilePath)[static::SETTING_IGNORED_ALIASES];
        }

        return [];
    }

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyleIo
     * @param array<string,string> $missingDirectAliases
     */
    private function printMissingDirectAliasesWarning(SymfonyStyle $symfonyStyleIo, array $missingDirectAliases): void
    {
        $symfonyStyleIo->warning(sprintf(
            'There are some indirect aliases to project services that should be declared directly for command "%s" to work correctly.',
            ExtendedClassesAnnotationsCommand::getDefaultName()
        ));
        $missingDirectAliasList = [];
        foreach ($missingDirectAliases as $aliasId => $serviceId) {
            $missingDirectAliasList[] = sprintf("%s\n    → %s", $aliasId, $serviceId);
        }
        $symfonyStyleIo->listing($missingDirectAliasList);
    }

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyleIo
     * @param array<string,string> $unambiguousMissingAliases
     */
    private function printUnambiguousPossibleMissingAliasesWarning(SymfonyStyle $symfonyStyleIo, array $unambiguousMissingAliases): void
    {
        $symfonyStyleIo->warning(sprintf(
            'There are some descendants of classes from "%s" in "%s" that are declared as independent services in DIC.'
            . ' Is it intentional, or did you forget to update services.yml?',
            static::FQCN_PREFIX_EXTENDED_SERVICES,
            static::FQCN_PREFIX_APPLICATION
        ));
        $missingServiceAliasList = [];
        foreach ($unambiguousMissingAliases as $aliasId => $serviceId) {
            $missingServiceAliasList[] = sprintf("%s\n    → %s", $aliasId, $serviceId);
        }
        $symfonyStyleIo->listing($missingServiceAliasList);
    }

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyleIo
     * @param array<string,string[]> $possibleMissingAliases
     */
    private function printAmbiguousPossibleMissingAliasesWarning(SymfonyStyle $symfonyStyleIo, array $possibleMissingAliases): void
    {
        $symfonyStyleIo->warning(sprintf(
            'There are some multiple descendants of classes from "%s" in "%s" that are declared as independent services in DIC.'
            . ' This probably means that the parent is a base class that should\'ve been declared as abstract class.'
            . ' If so, you can ignore them in "%s".',
            static::FQCN_PREFIX_EXTENDED_SERVICES,
            static::FQCN_PREFIX_APPLICATION,
            $this->settingYamlFilePath
        ));
        $missingServicesAliasList = [];
        foreach ($possibleMissingAliases as $aliasId => $serviceIds) {
            $missingServicesAliasList[] = sprintf("%s\n    → %s", $aliasId, implode("\n    → ", $serviceIds));
        }
        $symfonyStyleIo->listing($missingServicesAliasList);
    }

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyleIo
     * @param array<string,string> $missingDirectAliases
     * @param array<string,string> $unambiguousPossibleMissingAliases
     * @param array<string,string[]> $possibleMissingAliases
     */
    private function printRecommendation(SymfonyStyle $symfonyStyleIo, array $missingDirectAliases, array $unambiguousPossibleMissingAliases, array $possibleMissingAliases): void
    {
        $serviceConfigLines = [];
        foreach ($missingDirectAliases + $unambiguousPossibleMissingAliases as $aliasId => $serviceId) {
            $serviceConfigLines[] = sprintf('    %s:', $aliasId);
            $serviceConfigLines[] = sprintf('        alias: %s', $serviceId);
            if ($this->containerInfoRegistry->isServiceAccessible($aliasId)) {
                $serviceConfigLines[] = '        public: true';
            }
            $serviceConfigLines[] = '';
        }
        if ($serviceConfigLines !== []) {
            $symfonyStyleIo->title('Recommended addition to your "services.yml" config:');
            $symfonyStyleIo->writeln('<fg=yellow>services:</fg=yellow>');
            $symfonyStyleIo->writeln($serviceConfigLines);
        }

        if ($unambiguousPossibleMissingAliases !== []) {
            $symfonyStyleIo->note(
                'Your IDE might warn you that the key is duplicated in Yaml. This means that the service is already explicitly defined, but not as alias of the project service.'
            );
            $symfonyStyleIo->note(sprintf(
                "For validators (%s) it's recommended to change the class in the original service definition (%s*) and define an alias named after your own class (%s*)",
                ValidatorInterface::class,
                static::FQCN_PREFIX_EXTENDED_SERVICES,
                static::FQCN_PREFIX_APPLICATION
            ));
        }

        $settingYamlLines = [];
        foreach (array_keys($possibleMissingAliases) as $aliasId) {
            $settingYamlLines[] = sprintf('    - %s', $aliasId);
        }
        if ($settingYamlLines !== []) {
            $symfonyStyleIo->title(sprintf('Recommended addition to "%s":', $this->settingYamlFilePath));
            $symfonyStyleIo->writeln(sprintf('<fg=yellow>%s:</fg=yellow>', static::SETTING_IGNORED_ALIASES));
            $symfonyStyleIo->writeln($settingYamlLines);
        }

        $symfonyStyleIo->newLine(2);
    }
}
