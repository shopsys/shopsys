import { FormBlockWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useRegistrationFormMeta } from 'components/Pages/Registration/registrationFormMeta';
import { useFormContext } from 'react-hook-form';
import { RegistrationFormType } from 'types/form';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const RegistrationUser: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const formMeta = useRegistrationFormMeta(formProviderMethods);

    return (
        <FormBlockWrapper>
            <FormHeading>{t('Personal data')}</FormHeading>

            <TextInputControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.email.name}
                render={(textInput) => <FormLine>{textInput}</FormLine>}
                textInputProps={{
                    label: formMeta.fields.email.label,
                    required: true,
                    type: 'email',
                    autoComplete: 'email',
                    'aria-labelledby': 'registration-form-description',
                }}
            />

            <FormColumn>
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.firstName.name}
                    render={(textInput) => <FormLine className="col-span-2">{textInput}</FormLine>}
                    textInputProps={{
                        label: formMeta.fields.firstName.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'given-name',
                    }}
                />

                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.lastName.name}
                    render={(textInput) => <FormLine className="col-span-2">{textInput}</FormLine>}
                    textInputProps={{
                        label: formMeta.fields.lastName.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'family-name',
                    }}
                />
            </FormColumn>

            <FormColumn>
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.telephone.name}
                    render={(textInput) => <FormLine className="col-span-2">{textInput}</FormLine>}
                    textInputProps={{
                        label: formMeta.fields.telephone.label,
                        required: true,
                        type: 'tel',
                        autoComplete: 'tel',
                    }}
                />
            </FormColumn>
        </FormBlockWrapper>
    );
};
