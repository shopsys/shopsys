<?php

namespace Shopsys\FrameworkBundle\Component\Grid;

use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig_Environment;

class GridView
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    private $grid;

    /**
     * @var array
     */
    private $templateParameters;

    /**
     * @var \Twig_TemplateWrapper[]
     */
    private $templates;

    /**
     * @var string|string[]|null
     */
    private $theme;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @param string|string[] $theme
     */
    public function __construct(
        Grid $grid,
        RequestStack $requestStack,
        RouterInterface $router,
        Twig_Environment $twig,
        $theme,
        array $templateParameters = []
    ) {
        $this->grid = $grid;
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->twig = $twig;

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
                    ]
                );

                $templateParameters = $this->twig->mergeGlobals($parameters);

                if ($echo) {
                    echo $template->renderBlock($name, $templateParameters);
                    return;
                } else {
                    return $template->renderBlock($name, $templateParameters);
                }
            }
        }

        throw new \InvalidArgumentException(sprintf('Block "%s" doesn\'t exist in grid template "%s".', $name, $this->theme));
    }

    /**
     * @param array|null $row
     */
    public function renderCell(Column $column, array $row = null, FormView $formView = null)
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
     * @param array|string|null $removeParameters
     * @return string
     */
    public function getUrl(array $parameters = null, $removeParameters = null)
    {
        $masterRequest = $this->requestStack->getMasterRequest();
        $routeParameters = $this->grid->getUrlParameters($parameters, $removeParameters);

        return $this->router->generate(
            $masterRequest->attributes->get('_route'),
            $routeParameters,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    /**
     * @param string $name
     * @return bool
     */
    private function blockExists($name)
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
     */
    private function setTheme($theme, array $parameters = [])
    {
        $this->theme = $theme;
        $this->templateParameters = $parameters;
    }

    /**
     * @return \Twig_TemplateWrapper[]
     */
    private function getTemplates()
    {
        if (empty($this->templates)) {
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
     * @return \Twig_TemplateWrapper
     */
    private function getTemplateFromString($theme)
    {
        return $this->twig->load($theme);
    }

    /**
     * @param array $row
     * @return mixed
     */
    private function getCellValue(Column $column, $row)
    {
        return Grid::getValueFromRowBySourceColumnName($row, $column->getSourceColumnName());
    }

    /**
     * @param mixed $variable
     * @return string
     */
    private function getVariableType($variable)
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
