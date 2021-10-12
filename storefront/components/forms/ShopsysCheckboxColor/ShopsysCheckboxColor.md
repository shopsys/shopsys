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
        <ShopsysCheckboxColor
            id="my-form_checkbox-default"
            name="checkboxDefault"
            label="default"
        />
        <ShopsysCheckboxColor
            id="my-form_checkbox-required"
            name="checkboxRequired"
            label="required"
            required={true}
        />
    </form>
</FormProvider>;
```
