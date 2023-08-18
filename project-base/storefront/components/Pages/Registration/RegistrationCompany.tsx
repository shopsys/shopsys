import { Heading } from 'components/Basic/Heading/Heading';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useRegistrationFormMeta } from 'components/Pages/Registration/registrationFormMeta';
import useTranslation from 'next-translate/useTranslation';
import { useFormContext } from 'react-hook-form';
import { RegistrationFormType } from 'types/form';

export const RegistrationCompany: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const formMeta = useRegistrationFormMeta(formProviderMethods);

    return (
        <>
            <Heading type="h3">{t('Company data')}</Heading>
            <TextInputControlled
                control={formProviderMethods.control}
                name={formMeta.fields.companyName.name}
                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                formName={formMeta.formName}
                textInputProps={{
                    label: formMeta.fields.companyName.label,
                    required: true,
                    type: 'text',
                    autoComplete: 'organization',
                }}
            />
            <TextInputControlled
                control={formProviderMethods.control}
                name={formMeta.fields.companyNumber.name}
                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                formName={formMeta.formName}
                textInputProps={{
                    label: formMeta.fields.companyNumber.label,
                    required: true,
                    type: 'text',
                }}
            />
            <TextInputControlled
                control={formProviderMethods.control}
                name={formMeta.fields.companyTaxNumber.name}
                render={(textInput) => <FormLine>{textInput}</FormLine>}
                formName={formMeta.formName}
                textInputProps={{
                    label: formMeta.fields.companyTaxNumber.label,
                    required: false,
                    type: 'text',
                }}
            />
        </>
    );
};
