<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use InvalidArgumentException;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\TemplateWrapper;

class GridView
{
    /**
     * @var mixed[]
     */
    protected array $templateParameters;

    /**
     * @var \Twig\TemplateWrapper[]
     */
    protected array $templates = [];

    /**
     * @var string|string[]|null
     */
    protected string|array|null $theme = null;

    /**
     * @param string|string[] $theme
     */
    public function __construct(
        protected readonly Grid $grid,
        protected readonly RequestStack $requestStack,
        protected readonly RouterInterface $router,
        protected readonly Environment $twig,
        array|string $theme,
        array $templateParameters = [],
    ) {
        $this->setTheme($theme, $templateParameters);
    }

    public function render(): void
    {
        $this->renderBlock('grid');
    }

    public function renderHiddenInputs(array|string|null $removeParameters = null): void
    {
        $this->renderBlock('grid_hidden_inputs', [
            'parameter' => $this->grid->getUrlGridParameters(null, $removeParameters),
        ]);
    }

    public function renderBlock(string $name, array $parameters = [], bool $echo = true): ?string
    {
        foreach ($this->getTemplates() as $template) {
            if ($template->hasBlock($name)) {
                $parameters = array_merge(
                    $parameters,
                    $this->templateParameters,
                    [
                        'gridView' => $this,
                        'grid' => $this->grid,
                    ],
                );

                if ($echo) {
                    echo $template->renderBlock($name, $parameters);

                    return null;
                }

                return $template->renderBlock($name, $parameters);
            }
        }

        throw new InvalidArgumentException(
            sprintf('Block "%s" doesn\'t exist in grid template "%s".', $name, $this->theme),
        );
    }

    public function renderCell(Column $column, ?array $row = null, ?FormView $formView = null): void
    {
        if ($row !== null) {
            $value = $this->getCellValue($column, $row);
        } else {
            $value = null;
        }

        $blockParameters = [
            'value' => $value,
            'row' => $row,
            'column' => $column,
            'form' => $formView,
        ];

        if ($column->getTemplate() !== null) {
            echo $this->twig->render($column->getTemplate(), $blockParameters);

            return;
        }

        if ($formView === null) {
            $possibleBlocks = [
                'grid_value_cell_id_' . $column->getId(),
                'grid_value_cell_type_' . $this->getVariableType($value),
                'grid_value_cell',
            ];
        } else {
            $possibleBlocks = [
                'grid_value_cell_edit_id_' . $column->getId(),
                'grid_value_cell_edit_type_' . $this->getVariableType($value),
                'grid_value_cell_edit',
            ];
        }

        foreach ($possibleBlocks as $blockName) {
            if ($this->blockExists($blockName)) {
                $this->renderBlock($blockName, $blockParameters);

                break;
            }
        }
    }

    public function renderActionCell(ActionColumn|GridRowActionInterface $actionColumn, array $row): void
    {
        if ($actionColumn instanceof ActionColumn) {
            $possibleBlocks = [
                'grid_action_cell_type_' . $actionColumn->getType(),
                'grid_action_cell',
            ];

            foreach ($possibleBlocks as $blockName) {
                if ($this->blockExists($blockName)) {
                    $this->renderBlock($blockName, ['actionColumn' => $actionColumn, 'row' => $row]);

                    break;
                }
            }

            return;
        }

        // Clone action for each row to avoid state pollution between rows
        $rowAction = clone $actionColumn;

        if ($rowAction->validate($row) === false) {
            return;
        }

        $renderData = $rowAction->renderData();
        echo $this->twig->render($renderData['template'], [...$renderData['parameters'], 'row' => $row]);
    }

    public function renderTitleCell(Column $column): void
    {
        $possibleBlocks = [
            'grid_title_cell_id_' . $column->getId(),
            'grid_title_cell',
        ];

        foreach ($possibleBlocks as $blockName) {
            if ($this->blockExists($blockName)) {
                $this->renderBlock($blockName, ['column' => $column]);

                break;
            }
        }
    }

    public function getUrl(?array $parameters = null, array|string|null $removeParameters = null): string
    {
        $masterRequest = $this->requestStack->getMainRequest();
        $routeParameters = $this->grid->getUrlParameters($parameters, $removeParameters);

        return $this->router->generate(
            $masterRequest->attributes->get('_route'),
            $routeParameters,
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }

    protected function blockExists(string $name): bool
    {
        foreach ($this->getTemplates() as $template) {
            if ($template->hasBlock($name)) {
                return true;
            }
        }

        return false;
    }

    public function getTheme(): array|string|null
    {
        return $this->theme;
    }

    /**
     * @param string[]|string $theme
     * @param mixed[] $parameters
     */
    protected function setTheme(array|string $theme, array $parameters = []): void
    {
        $this->theme = $theme;
        $this->templateParameters = $parameters;
    }

    /**
     * @return \Twig\TemplateWrapper[]
     */
    protected function getTemplates(): array
    {
        if (count($this->templates) === 0) {
            $this->templates = [];

            if (is_array($this->theme)) {
                foreach ($this->theme as $theme) {
                    $this->templates[] = $this->getTemplateFromString($theme);
                }
            } else {
                $this->templates[] = $this->getTemplateFromString($this->theme);
            }
        }

        return $this->templates;
    }

    protected function getTemplateFromString(string $theme): TemplateWrapper
    {
        return $this->twig->load($theme);
    }

    protected function getCellValue(Column $column, array $row): mixed
    {
        return $this->grid->getValueFromRowBySourceColumnName($row, $column->getSourceColumnName());
    }

    protected function getVariableType(mixed $variable): string
    {
        return match (gettype($variable)) {
            'boolean' => 'boolean',
            'integer', 'double' => 'number',
            'object' => str_replace('\\', '_', get_class($variable)),
            'string' => 'string',
            'NULL' => 'null',
            default => 'unknown',
        };
    }
}
