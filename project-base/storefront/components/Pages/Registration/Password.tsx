import { Heading } from 'components/Basic/Heading/Heading';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { PasswordInputControlled } from 'components/Forms/TextInput/PasswordInputControlled';
import { useRegistrationFormMeta } from 'components/Pages/Registration/formMeta';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useFormContext } from 'react-hook-form';
import { RegistrationFormType } from 'types/form';

export const Password: FC = () => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const formMeta = useRegistrationFormMeta(formProviderMethods);

    return (
        <>
            <Heading type="h3">{t('Create a password')}</Heading>
            <FormColumn>
                <PasswordInputControlled
                    control={formProviderMethods.control}
                    name={formMeta.fields.passwordFirst.name}
                    render={(passwordInput) => (
                        <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                            {passwordInput}
                        </FormLine>
                    )}
                    formName={formMeta.formName}
                    passwordInputProps={{
                        label: formMeta.fields.passwordFirst.label,
                    }}
                />
                <PasswordInputControlled
                    control={formProviderMethods.control}
                    name={formMeta.fields.passwordSecond.name}
                    render={(passwordInput) => (
                        <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                            {passwordInput}
                        </FormLine>
                    )}
                    formName={formMeta.formName}
                    passwordInputProps={{
                        label: formMeta.fields.passwordSecond.label,
                    }}
                />
            </FormColumn>
        </>
    );
};
