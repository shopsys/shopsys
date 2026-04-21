import { FormBlockWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { useRegistrationFormMeta } from 'components/Pages/Registration/registrationFormMeta';
import { useFormContext } from 'react-hook-form';
import { RegistrationFormType } from 'types/form';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const RegistrationPassword: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const formMeta = useRegistrationFormMeta();

    return (
        <FormBlockWrapper>
            <FormHeading>{t('Password')}</FormHeading>

            <FormColumn>
                <PasswordInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.password.name}
                    width="half"
                    passwordInputProps={{
                        label: formMeta.fields.password.label,
                        autoComplete: 'new-password',
                    }}
                />

                <PasswordInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.passwordConfirm.name}
                    width="half"
                    passwordInputProps={{
                        label: formMeta.fields.passwordConfirm.label,
                    }}
                />
            </FormColumn>
        </FormBlockWrapper>
    );
};
