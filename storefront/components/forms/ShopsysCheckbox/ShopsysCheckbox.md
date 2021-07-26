```jsx
import * as Yup from 'yup';
import { FormProvider, useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';

const validationSchema = Yup.object().shape({
    checkboxRequired: Yup.bool().oneOf([true], 'Checkbox is required'),
});

const formProviderMethods = useForm({
    mode: 'onChange',
    defaultValues: {
        /**
         * These values get mapped into the initial checked state of checkboxes
         * true = checked
         * false = unchecked
        */
        checkboxDefault: false,
        checkboxRequired: false,
        checkboxChecked: true,
        checkboxDisabled: false,
        checkboxDisabledChecked: true,
        checkboxWithLink: false,
    },
    criteriaMode: 'firstError',
    shouldFocusError: true,
    resolver: yupResolver(validationSchema),
});

<FormProvider {...formProviderMethods}>
    <form style={{ width: '250px' }}>
        <ShopsysCheckbox
            id="my-form_checkbox-default"
            name="checkboxDefault"
            label="default"
        />
        <ShopsysCheckbox
            id="my-form_checkbox-required"
            name="checkboxRequired"
            label="required"
            required={true}
        />
        <ShopsysCheckbox
            id="my-form_checkbox-checked"
            name="checkboxChecked"
            label="checked"
        />
        <ShopsysCheckbox
            id="my-form_checkbox-disabled"
            name="checkboxDisabled"
            label="disabled"
            disabled={true}
        />
        <ShopsysCheckbox
            id="my-form_checkbox-disabled"
            name="checkboxDisabledChecked"
            label="disabled checked"
            disabled={true}
        />
        <ShopsysCheckbox
            id="my-form_checkbox-with-link"
            name="checkboxWithLink"
            label={<a href="#">this is a link</a>}
        />
    </form>
</FormProvider>;
```
