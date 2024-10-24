import { FormHeading, FormBlockWrapper } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useCustomerChangeProfileFormMeta } from 'components/Pages/Customer/customerChangeProfileFormMeta';
import useTranslation from 'next-translate/useTranslation';
import { useFormContext } from 'react-hook-form';
import { CustomerChangeProfileFormType } from 'types/form';
import { useUserPermissions } from 'utils/auth/useUserPermissions';

export const CompanyCustomer: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<CustomerChangeProfileFormType>();
    const formMeta = useCustomerChangeProfileFormMeta(formProviderMethods);
    const { canManageProfile } = useUserPermissions();

    return (
        <FormBlockWrapper>
            <>
                <FormHeading>{t('Company information')}</FormHeading>
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.companyName.name}
                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                    textInputProps={{
                        label: formMeta.fields.companyName.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'organization',
                        disabled: !canManageProfile,
                    }}
                />
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.companyNumber.name}
                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                    textInputProps={{
                        label: formMeta.fields.companyNumber.label,
                        required: true,
                        type: 'text',
                        disabled: !canManageProfile,
                    }}
                />
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.companyTaxNumber.name}
                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                    textInputProps={{
                        label: formMeta.fields.companyTaxNumber.label,
                        required: false,
                        type: 'text',
                        disabled: !canManageProfile,
                    }}
                />
            </>
        </FormBlockWrapper>
    );
};
