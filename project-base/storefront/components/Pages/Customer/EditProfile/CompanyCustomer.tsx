import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useCustomerChangeProfileFormMeta } from 'components/Pages/Customer/EditProfile/customerChangeProfileFormMeta';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useFormContext } from 'react-hook-form';
import { CustomerChangeProfileFormType } from 'types/form';

export const CompanyCustomer: FC = () => {
    const formProviderMethods = useFormContext<CustomerChangeProfileFormType>();
    const formMeta = useCustomerChangeProfileFormMeta();
    const { canManageCompanyData } = useAuthorization();

    return (
        <div className="flex flex-col gap-5">
            <TextInputControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.companyName.name}
                textInputProps={{
                    label: formMeta.fields.companyName.label,
                    required: true,
                    type: 'text',
                    autoComplete: 'organization',
                    disabled: !canManageCompanyData,
                }}
            />

            <FormColumn>
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.companyNumber.name}
                    width="half"
                    textInputProps={{
                        label: formMeta.fields.companyNumber.label,
                        required: true,
                        type: 'text',
                        disabled: !canManageCompanyData,
                    }}
                />

                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.companyTaxNumber.name}
                    width="half"
                    textInputProps={{
                        label: formMeta.fields.companyTaxNumber.label,
                        required: false,
                        type: 'text',
                        disabled: !canManageCompanyData,
                    }}
                />
            </FormColumn>
        </div>
    );
};
