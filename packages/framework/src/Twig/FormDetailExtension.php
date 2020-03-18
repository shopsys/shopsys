<?php

namespace Shopsys\FrameworkBundle\Twig;

use Symfony\Component\Form\FormView;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FormDetailExtension extends AbstractExtension
{
    /**
     * @var \Twig\Environment
     */
    protected $twigEnvironment;

    /**
     * @param \Twig\Environment $twigEnvironment
     */
    public function __construct(Environment $twigEnvironment)
    {
        $this->twigEnvironment = $twigEnvironment;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('form_id', [$this, 'formId'], ['is_safe' => ['html']]),
            new TwigFunction('form_save', [$this, 'formSave'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param mixed $object
     * @return string
     */
    public function formId($object)
    {
        if ($object === null) {
            return '';
        } else {
            return '<div class="form-line">
                        <label class="form-line__label">ID:</label>
                        <div class="form-line__side">
                            <div class="form-line__item">
                                <input
                                    type="text"
                                    value="' . htmlspecialchars($object->getId(), ENT_QUOTES) . '"
                                    class="input"
                                    readonly="readonly"
                                >
                            </div>
                        </div>
                    </div>';
        }
    }

    /**
     * @param mixed $object
     * @param \Symfony\Component\Form\FormView $formView
     * @param array $vars
     * @return string
     */
    public function formSave($object, FormView $formView, array $vars = [])
    {
        $template = $this->twigEnvironment->createTemplate('{{ form_widget(form.save, vars) }}');

        if (!array_keys($vars, 'label', true)) {
            if ($object === null) {
                $vars['label'] = t('Create');
            } else {
                $vars['label'] = t('Save changes');
            }
        }

        $parameters['form'] = $formView;
        $parameters['vars'] = $vars;

        return $template->render($parameters);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'shopsys.twig.form_detail_extension';
    }
}
