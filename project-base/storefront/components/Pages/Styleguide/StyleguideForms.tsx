import { StyleguideSection } from './StyleguideElements';
import { yupResolver } from '@hookform/resolvers/yup';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { Translate } from 'next-translate';
import useTranslation from 'next-translate/useTranslation';
import { FormProvider } from 'react-hook-form';
import { useShopsysForm } from 'utils/forms/useShopsysForm';
import * as Yup from 'yup';

export const StyleguideForms: FC = () => {
    return (
        <StyleguideSection className="flex flex-col gap-3" title="Forms">
            <StyleguideFormExample />
        </StyleguideSection>
    );
};

export const StyleguideFormExample: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useShopsysForm(getStyleguideExampleFormResolver(t), {
        optionalValue: '',
        requiredValue: '',
    });

    const onValidForm = () => {
        // eslint-disable-next-line no-alert
        alert('Valid form handler');
    };

    const onInvalidForm = () => {
        // eslint-disable-next-line no-alert
        alert('Invalid form handler');
    };

    return (
        <FormProvider {...formProviderMethods}>
            <Form onSubmit={formProviderMethods.handleSubmit(onValidForm, onInvalidForm)}>
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName="example-form"
                    name="optionalValue"
                    render={(optionalValue) => <FormLine bottomGap>{optionalValue}</FormLine>}
                    textInputProps={{
                        label: 'Optional Value',
                        type: 'text',
                    }}
                />
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName="example-form"
                    name="requiredValue"
                    render={(requiredInput) => <FormLine>{requiredInput}</FormLine>}
                    textInputProps={{
                        label: 'Required value',
                        required: true,
                        type: 'text',
                    }}
                />
                <div className="mt-3">
                    <SubmitButton>Submit form</SubmitButton>
                </div>
            </Form>
        </FormProvider>
    );
};

const getStyleguideExampleFormResolver = (t: Translate) => {
    return yupResolver(
        Yup.object().shape<Record<keyof { optionalValue: string; requiredValue: string }, any>>({
            optionalValue: Yup.string(),
            requiredValue: Yup.string().required(t('This field is required')),
        }),
    );
};
