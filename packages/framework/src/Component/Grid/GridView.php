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
     * @param array $templateParameters
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

    public function render()
    {
        $this->renderBlock('grid');
    }

    /**
     * @param array|null $removeParameters
     */
    public function renderHiddenInputs($removeParameters = null)
    {
        $this->renderBlock('grid_hidden_inputs', [
            'parameter' => $this->grid->getUrlGridParameters(null, $removeParameters),
        ]);
    }

    /**
     * @param string $name
     * @param array $parameters
     * @param bool $echo
     * @return string|null
     */
    public function renderBlock($name, array $parameters = [], $echo = true)
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
    public function renderCell(Column $column, ?array $row = null, ?FormView $formView = null)
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
     * @param array $row
     */
    public function renderActionCell(ActionColumn $actionColumn, array $row)
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
    public function renderTitleCell(Column $column)
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
     * @param array $parameters
     * @param array|string|null $removeParameters
     * @return string
     */
    public function getUrl(?array $parameters = null, $removeParameters = null)
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
    protected function blockExists($name)
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
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param string|string[] $theme
     * @param array $parameters
     */
    protected function setTheme($theme, array $parameters = [])
    {
        $this->theme = $theme;
        $this->templateParameters = $parameters;
    }

    /**
     * @return \Twig\TemplateWrapper[]
     */
    protected function getTemplates()
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
    protected function getTemplateFromString($theme)
    {
        return $this->twig->load($theme);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\Column $column
     * @param array $row
     * @return mixed
     */
    protected function getCellValue(Column $column, $row)
    {
        return Grid::getValueFromRowBySourceColumnName($row, $column->getSourceColumnName());
    }

    /**
     * @param mixed $variable
     * @return string
     */
    protected function getVariableType($variable)
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
