```jsx
import * as Yup from 'yup';
import { FormProvider, useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';

const validationSchema = Yup.object().shape({
    name: Yup.string()
        .notRequired()
        .matches(
            /^[a-zA-ZàáâäãåąčćęèéêëėįìíîïłńòóôöõøùúûüųūÿýżźñçčšžÀÁÂÄÃÅĄĆČĖĘÈÉÊËÌÍÎÏĮŁŃÒÓÔÖÕØÙÚÛÜŲŪŸÝŻŹÑßÇŒÆČŠŽ∂ð ,.'-]+$/u,
            {
                message: 'name cannot contain special characters or numbers',
                excludeEmptyString: true,
            },
        ),
});

const formProviderMethods = useForm({
    mode: 'onBlur',
    criteriaMode: 'firstError',
    shouldFocusError: true,
    resolver: yupResolver(validationSchema),
});

<FormProvider {...formProviderMethods}>
    <form>
        <ShopsysTextInput id="my-form_name" name="name" label={'name'} markSuccessfulWhenValid={true} />
        <ShopsysTextInput id="my-form_password" name="password" label={'password'} type="password" />
        <ShopsysTextInput id="my-form_disabled" name="disabled" label={'disabled'} disabled={true} />
    </form>
</FormProvider>;
```
