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
        <>
            <ShopsysTextarea
                id="my_form-textarea"
                name="textarea"
                label={'I get green when you touch me'}
                markSuccessfulWhenValid={true}
            />
            <ShopsysTextarea id="my_form-textarea_required" name="required" required={true} label={'required'} />
            <ShopsysTextarea id="my_form-textarea_disabled" name="disabled" label={'disabled'} disabled={true} />
            <ShopsysTextarea id="my_form-textarea_small" name="small" rows={2} label={'2 rows'} />
        </>
    </form>
</FormProvider>;
```
