import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useRegistrationFormMeta } from 'components/Pages/Registration/registrationFormMeta';
import { useFormContext } from 'react-hook-form';
import { RegistrationFormType } from 'types/form';

export const RegistrationCompany: FC = () => {
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const formMeta = useRegistrationFormMeta();

    return (
        <>
            <TextInputControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.companyName.name}
                textInputProps={{
                    label: formMeta.fields.companyName.label,
                    required: true,
                    type: 'text',
                    autoComplete: 'organization',
                }}
            />

            <FormColumn>
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    gridClassName="col-span-2"
                    name={formMeta.fields.companyNumber.name}
                    textInputProps={{
                        label: formMeta.fields.companyNumber.label,
                        required: true,
                        type: 'text',
                    }}
                />

                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    gridClassName="col-span-2"
                    name={formMeta.fields.companyTaxNumber.name}
                    textInputProps={{
                        label: formMeta.fields.companyTaxNumber.label,
                        required: false,
                        type: 'text',
                    }}
                />
            </FormColumn>
        </>
    );
};
