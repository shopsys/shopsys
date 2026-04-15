import { yupResolver } from '@hookform/resolvers/yup';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { Translate } from 'next-translate';
import { FormProvider } from 'react-hook-form';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';
import { StyleguideSection } from './StyleguideElements';

export const StyleguideForms: FC = () => {
    return (
        <StyleguideSection className="flex max-w-96 flex-col gap-3" title="Forms">
            <StyleguideFormExample />
        </StyleguideSection>
    );
};

const StyleguideFormExample: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useFormWrapper(getStyleguideExampleFormResolver(t), {
        optionalValue: '',
        requiredValue: '',
    });

    const onValidForm = () => {
        alert('Valid form handler');
    };

    const onInvalidForm = () => {
        alert('Invalid form handler');
    };

    return (
        <FormProvider {...formProviderMethods}>
            <Form formName="example-form" onSubmit={formProviderMethods.handleSubmit(onValidForm, onInvalidForm)}>
                <FormContentWrapper>
                    <FormBlockWrapper>
                        <FormHeading>Example form</FormHeading>

                        <TextInputControlled
                            control={formProviderMethods.control}
                            formName="example-form"
                            name="optionalValue"
                            render={(optionalValue) => <FormLine>{optionalValue}</FormLine>}
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
                    </FormBlockWrapper>

                    <FormButtonWrapper>
                        <SubmitButton>Submit form</SubmitButton>
                    </FormButtonWrapper>
                </FormContentWrapper>
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
