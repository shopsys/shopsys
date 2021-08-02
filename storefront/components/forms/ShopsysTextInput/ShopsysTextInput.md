```jsx
import * as Yup from 'yup';
import { FormProvider, useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';

const validationSchema = Yup.object().shape({
    required: Yup.string().required('This is a required field'),
});

const formProviderMethods = useForm({
    mode: 'onBlur',
    criteriaMode: 'firstError',
    shouldFocusError: true,
    resolver: yupResolver(validationSchema),
});

<FormProvider {...formProviderMethods}>
    <form>
        <ShopsysTextInput
            id="my_form-input_required"
            name="required"
            label={'required'}
            required={true}
            markSuccessfulWhenValid={true}
        />
        <ShopsysTextInput
            id="my_form-input_success"
            name="success"
            label={'I get green when you touch me'}
            markSuccessfulWhenValid={true}
        />
        <ShopsysTextInput id="my_form_password" name="password" label={'password'} type="password" />
        <ShopsysTextInput id="my_form_disabled" name="disabled" label={'disabled'} disabled={true} />
    </form>
</FormProvider>;
```
