import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { Textarea } from 'components/Forms/Textarea/Textarea';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { TIDs } from 'cypress/tids';
import { Translate } from 'next-translate';
import { ChangeEvent, useState } from 'react';
import { FormProvider } from 'react-hook-form';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import { yupResolver } from 'utils/forms/yupResolver';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';
import { StyleguidePreview, StyleguideSpecimen, StyleguideSubSection } from './StyleguideElements';

export const StyleguideForms: FC = () => (
    <>
        <StyleguideFieldStates />

        <StyleguideSubSection description="Submit the empty form to see the error state." title="Form layout">
            <StyleguidePreview className="max-w-xl">
                <StyleguideFormExample />
            </StyleguidePreview>
        </StyleguideSubSection>
    </>
);

const FILLED_VALUE = 'jan.novak@email.cz';

const ignoreChange = () => undefined;

const StyleguideFieldStates: FC = () => {
    const [email, setEmail] = useState('');
    const [smallValue, setSmallValue] = useState('');
    const [note, setNote] = useState('');

    const handleEmailChange = (event: ChangeEvent<HTMLInputElement>) => setEmail(event.target.value);
    const handleSmallValueChange = (event: ChangeEvent<HTMLInputElement>) => setSmallValue(event.target.value);
    const handleNoteChange = (event: ChangeEvent<HTMLTextAreaElement>) => setNote(event.target.value);

    return (
        <StyleguideSubSection
            description="Hover darkens the border, a click thickens it to 2px. Only keyboard focus (Tab) fills the field, like buttons and links."
            title="Field states"
        >
            <StyleguidePreview className="grid items-start gap-6 md:grid-cols-2 xl:grid-cols-3">
                <StyleguideSpecimen className="w-full" label="default">
                    <div data-tid={TIDs.styleguide_field_default}>
                        <TextInput
                            id="styleguide-field-default"
                            label="E-mail"
                            value={email}
                            onChange={handleEmailChange}
                        />
                    </div>
                </StyleguideSpecimen>
                <StyleguideSpecimen className="w-full" label="filled">
                    <TextInput
                        id="styleguide-field-filled"
                        label="E-mail"
                        value={FILLED_VALUE}
                        onChange={ignoreChange}
                    />
                </StyleguideSpecimen>
                <StyleguideSpecimen className="w-full" label="hasError">
                    <TextInput
                        hasError
                        id="styleguide-field-error"
                        label="E-mail"
                        value="jan.novak@"
                        onChange={ignoreChange}
                    />
                </StyleguideSpecimen>
                <StyleguideSpecimen className="w-full" label="hasWarning">
                    <TextInput
                        hasWarning
                        id="styleguide-field-warning"
                        label="E-mail"
                        value="jan@gmial.com"
                        onChange={ignoreChange}
                    />
                </StyleguideSpecimen>
                <StyleguideSpecimen className="w-full" label="disabled">
                    <TextInput
                        disabled
                        id="styleguide-field-disabled"
                        label="E-mail"
                        value={FILLED_VALUE}
                        onChange={ignoreChange}
                    />
                </StyleguideSpecimen>
                <StyleguideSpecimen className="w-full" hint="as high as a medium button" label='inputSize="small"'>
                    <div data-tid={TIDs.styleguide_field_small}>
                        <TextInput
                            id="styleguide-field-small"
                            inputSize="small"
                            label="Small field"
                            value={smallValue}
                            onChange={handleSmallValueChange}
                        />
                    </div>
                </StyleguideSpecimen>
                <StyleguideSpecimen className="w-full" label="Textarea">
                    <Textarea
                        hasError={false}
                        id="styleguide-field-textarea"
                        label="Note"
                        rows={3}
                        value={note}
                        onChange={handleNoteChange}
                    />
                </StyleguideSpecimen>
            </StyleguidePreview>
        </StyleguideSubSection>
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
    return yupResolver<{ optionalValue: string; requiredValue: string }>(
        Yup.object().shape<Record<keyof { optionalValue: string; requiredValue: string }, any>>({
            optionalValue: Yup.string(),
            requiredValue: Yup.string().required(t('This field is required')),
        }),
    );
};
