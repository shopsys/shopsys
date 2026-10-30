<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Search;

use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Builds the quick search form of a datagrid — the quick search form of the administration, only named per datagrid —
 * and reads the searched text through it, so that it is trimmed the way the form trims it.
 */
class QuickSearchFormFactory
{
    public function __construct(
        protected readonly FormFactoryInterface $formFactory,
    ) {
    }

    /**
     * @param string|null $term The text the form is filled with
     */
    public function createForm(string $formName, ?string $term = null): FormInterface
    {
        $data = new QuickSearchFormData();
        $data->text = $term;

        return $this->formFactory->createNamed($formName, QuickSearchFormType::class, $data);
    }

    /**
     * The form is submitted from the query by hand, see `DatagridStateEvent::getQuery()`.
     *
     * @param array<string, mixed> $query The query of the form
     * @return string|null The searched text, null when nothing was searched for
     */
    public function getTerm(string $formName, array $query): ?string
    {
        $form = $this->createForm($formName);
        $form->submit($query);
        $data = $form->getData();

        if ($data instanceof QuickSearchFormData === false || $data->text === null) {
            return null;
        }

        $term = trim($data->text);

        return $term === '' ? null : $term;
    }
}
