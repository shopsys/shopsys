import { Heading } from 'components/Basic/Heading/Heading';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useFormContext } from 'react-hook-form';
import { ContactInformation } from 'store/zustand/slices/createContactInformationSlice';
import { usePersistStore } from 'store/zustand/usePersistStore';

export const ContactInformationUser: FC = () => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);

    return (
        <>
            <Heading type="h3">{t('Customer information')}</Heading>
            <TextInputControlled
                control={formProviderMethods.control}
                name={formMeta.fields.telephone.name}
                render={(textInput) => (
                    <FormLine bottomGap className="flex-none lg:w-[65%]">
                        {textInput}
                    </FormLine>
                )}
                formName={formMeta.formName}
                textInputProps={{
                    label: formMeta.fields.telephone.label,
                    required: true,
                    type: 'tel',
                    autoComplete: 'tel',
                    onBlur: (event) => updateContactInformation({ telephone: event.currentTarget.value }),
                }}
            />
            <FormColumn className="lg:w-[calc(65%+0.75rem)]">
                <TextInputControlled
                    control={formProviderMethods.control}
                    name={formMeta.fields.firstName.name}
                    render={(textInput) => (
                        <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                            {textInput}
                        </FormLine>
                    )}
                    formName={formMeta.formName}
                    textInputProps={{
                        label: formMeta.fields.firstName.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'given-name',
                        onBlur: (event) => updateContactInformation({ firstName: event.currentTarget.value }),
                    }}
                />
                <TextInputControlled
                    control={formProviderMethods.control}
                    name={formMeta.fields.lastName.name}
                    render={(textInput) => (
                        <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                            {textInput}
                        </FormLine>
                    )}
                    formName={formMeta.formName}
                    textInputProps={{
                        label: formMeta.fields.lastName.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'family-name',
                        onBlur: (event) => updateContactInformation({ lastName: event.currentTarget.value }),
                    }}
                />
            </FormColumn>
        </>
    );
};
