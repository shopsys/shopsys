import { Heading } from 'components/Basic/Heading/Heading';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import useTranslation from 'next-translate/useTranslation';
import { useFormContext, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';

export const ContactInformationCompany: FC = () => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const [companyNameValue, companyNumberValue, companyTaxNumberValue] = useWatch({
        name: [
            formMeta.fields.companyName.name,
            formMeta.fields.companyNumber.name,
            formMeta.fields.companyTaxNumber.name,
        ],
        control: formProviderMethods.control,
    });

    return (
        <>
            <Heading type="h3">{t('Company data')}</Heading>
            <TextInputControlled
                control={formProviderMethods.control}
                name={formMeta.fields.companyName.name}
                render={(textInput) => (
                    <FormLine bottomGap className="flex-none lg:w-[65%]">
                        {textInput}
                    </FormLine>
                )}
                formName={formMeta.formName}
                textInputProps={{
                    label: formMeta.fields.companyName.label,
                    required: true,
                    type: 'text',
                    autoComplete: 'organization',
                    onBlur: () => updateContactInformation({ companyName: companyNameValue }),
                }}
            />
            <TextInputControlled
                control={formProviderMethods.control}
                name={formMeta.fields.companyNumber.name}
                render={(textInput) => (
                    <FormLine bottomGap className="flex-none lg:w-[65%]">
                        {textInput}
                    </FormLine>
                )}
                formName={formMeta.formName}
                textInputProps={{
                    label: formMeta.fields.companyNumber.label,
                    required: true,
                    type: 'text',
                    onBlur: () => updateContactInformation({ companyNumber: companyNumberValue }),
                }}
            />
            <TextInputControlled
                control={formProviderMethods.control}
                name={formMeta.fields.companyTaxNumber.name}
                render={(textInput) => (
                    <FormLine bottomGap className="flex-none lg:w-[65%]">
                        {textInput}
                    </FormLine>
                )}
                formName={formMeta.formName}
                textInputProps={{
                    label: formMeta.fields.companyTaxNumber.label,
                    required: false,
                    type: 'text',
                    onBlur: () => updateContactInformation({ companyTaxNumber: companyTaxNumberValue }),
                }}
            />
        </>
    );
};
