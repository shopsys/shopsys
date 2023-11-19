<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use InvalidArgumentException;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

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
     * @param \Shopsys\FrameworkBundle\Component\Grid\Grid $grid
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Twig\Environment $twig
     * @param string|string[] $theme
     * @param mixed[] $templateParameters
     */
    public function __construct(
        protected readonly Grid $grid,
        protected readonly RequestStack $requestStack,
        protected readonly RouterInterface $router,
        protected readonly Environment $twig,
        $theme,
        array $templateParameters = [],
    ) {
        $this->setTheme($theme, $templateParameters);
    }

    public function render(): void
    {
        $this->renderBlock('grid');
    }

    /**
     * @param array|null $removeParameters
     */
    public function renderHiddenInputs($removeParameters = null): void
    {
        $this->renderBlock('grid_hidden_inputs', [
            'parameter' => $this->grid->getUrlGridParameters(null, $removeParameters),
        ]);
    }

    /**
     * @param string $name
     * @param mixed[] $parameters
     * @param bool $echo
     * @return string|null
     */
    public function renderBlock($name, array $parameters = [], $echo = true): ?string
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

                $templateParameters = $this->twig->mergeGlobals($parameters);

                if ($echo) {
                    echo $template->renderBlock($name, $templateParameters);

                    return null;
                }

                return $template->renderBlock($name, $templateParameters);
            }
        }

        throw new InvalidArgumentException(
            sprintf('Block "%s" doesn\'t exist in grid template "%s".', $name, $this->theme),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\Column $column
     * @param array|null $row
     * @param \Symfony\Component\Form\FormView|null $formView
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\ActionColumn $actionColumn
     * @param mixed[] $row
     */
    public function renderActionCell(ActionColumn $actionColumn, array $row): void
    {
        $posibleBlocks = [
            'grid_action_cell_type_' . $actionColumn->getType(),
            'grid_action_cell',
        ];

        foreach ($posibleBlocks as $blockName) {
            if ($this->blockExists($blockName)) {
                $this->renderBlock($blockName, ['actionColumn' => $actionColumn, 'row' => $row]);

                break;
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\Column $column
     */
    public function renderTitleCell(Column $column): void
    {
        $posibleBlocks = [
            'grid_title_cell_id_' . $column->getId(),
            'grid_title_cell',
        ];

        foreach ($posibleBlocks as $blockName) {
            if ($this->blockExists($blockName)) {
                $this->renderBlock($blockName, ['column' => $column]);

                break;
            }
        }
    }

    /**
     * @param mixed[] $parameters
     * @param array|string|null $removeParameters
     * @return string
     */
    public function getUrl(?array $parameters = null, $removeParameters = null): string
    {
        $masterRequest = $this->requestStack->getMainRequest();
        $routeParameters = $this->grid->getUrlParameters($parameters, $removeParameters);

        return $this->router->generate(
            $masterRequest->attributes->get('_route'),
            $routeParameters,
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function blockExists($name): bool
    {
        foreach ($this->getTemplates() as $template) {
            if ($template->hasBlock($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string|array
     */
    public function getTheme(): string|array|null
    {
        return $this->theme;
    }

    /**
     * @param string|string[] $theme
     * @param mixed[] $parameters
     */
    protected function setTheme(string|array|null $theme, array $parameters = []): void
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

    /**
     * @param string $theme
     * @return \Twig\TemplateWrapper
     */
    protected function getTemplateFromString($theme): \Twig\TemplateWrapper
    {
        return $this->twig->load($theme);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\Column $column
     * @param mixed[] $row
     * @return mixed
     */
    protected function getCellValue(Column $column, array $row)
    {
        return Grid::getValueFromRowBySourceColumnName($row, $column->getSourceColumnName());
    }

    /**
     * @param mixed $variable
     * @return string
     */
    protected function getVariableType($variable): string
    {
        switch (gettype($variable)) {
            case 'boolean':
                return 'boolean';
            case 'integer':
            case 'double':
                return 'number';
            case 'object':
                return str_replace('\\', '_', get_class($variable));
            case 'string':
                return 'string';
            case 'NULL':
                return 'null';
            default:
                return 'unknown';
        }
    }
}
