import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { useRegistrationFormMeta } from 'components/Pages/Registration/registrationFormMeta';
import useTranslation from 'next-translate/useTranslation';
import { useFormContext } from 'react-hook-form';
import { RegistrationFormType } from 'types/form';

export const RegistrationPassword: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const formMeta = useRegistrationFormMeta(formProviderMethods);

    return (
        <>
            <div className="h4 mb-3">{t('Create a password')}</div>
            <FormColumn>
                <PasswordInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.passwordFirst.name}
                    passwordInputProps={{
                        label: formMeta.fields.passwordFirst.label,
                    }}
                    render={(passwordInput) => (
                        <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                            {passwordInput}
                        </FormLine>
                    )}
                />
                <PasswordInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.passwordSecond.name}
                    passwordInputProps={{
                        label: formMeta.fields.passwordSecond.label,
                    }}
                    render={(passwordInput) => (
                        <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                            {passwordInput}
                        </FormLine>
                    )}
                />
            </FormColumn>
        </>
    );
};
