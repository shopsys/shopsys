import { FormBlockWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { PhoneNumberInputControlled } from 'components/Forms/PhonePrefixSelect/PhoneNumberInputControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useRegistrationFormMeta } from 'components/Pages/Registration/registrationFormMeta';
import { useFormContext } from 'react-hook-form';
import { RegistrationFormType } from 'types/form';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const RegistrationUser: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const formMeta = useRegistrationFormMeta();

    return (
        <FormBlockWrapper>
            <FormHeading>{t('Personal data')}</FormHeading>

            <TextInputControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.email.name}
                textInputProps={{
                    label: formMeta.fields.email.label,
                    required: true,
                    type: 'email',
                    autoComplete: 'email',
                    'aria-describedby': 'registration-form-description',
                }}
            />

            <FormColumn>
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.firstName.name}
                    width="half"
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
                    width="half"
                    textInputProps={{
                        label: formMeta.fields.lastName.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'family-name',
                    }}
                />
            </FormColumn>

            <PhoneNumberInputControlled
                formName={formMeta.formName}
                formProviderMethods={formProviderMethods}
                prefixCountryCodeName={formMeta.fields.telephonePrefixCountryCode.name}
                prefixName={formMeta.fields.telephonePrefix.name}
                telephoneLabel={formMeta.fields.telephone.label}
                telephoneName={formMeta.fields.telephone.name}
            />
        </FormBlockWrapper>
    );
};
