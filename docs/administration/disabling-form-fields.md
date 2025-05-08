# Disabling form fields

During project implementation, it is usually necessary to import some fields from external systems.
Those imported fields do not need to (or even must not) be changeable in administration.
For this purpose, you can configure fields which should be disabled in the form.

## Allow form fields disabling for non-superadmin administrators

For the testing purposes, the fields are never disabled for administrators with `ROLE_SUPER_ADMIN`.
To allow disabling defined form fields for the rest of administrators, you need to:
- set ENV variable `DISABLE_FORM_FIELDS_FROM_TRANSFER` to true.
- call `FormBuilderHelper::disableFieldsByConfigurations()` with an array of fields names you want to disable in the `buildForm` method your form type extension, e.g.:
```php
<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Override;
use Shopsys\FrameworkBundle\Component\Form\FormBuilderHelper;
use Shopsys\FrameworkBundle\Form\Admin\Category\CategoryFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class CategoryFormTypeExtension extends AbstractTypeExtension
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Form\FormBuilderHelper $formBuilderHelper
     */
    public function __construct(
        private readonly FormBuilderHelper $formBuilderHelper,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->formBuilderHelper->disableFieldsByConfigurations($builder, ['name', 'descriptions']);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getExtendedTypes(): iterable
    {
        yield CategoryFormType::class;
    }
}
```

