import { FormBlockWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { CountrySelectControlled } from 'components/Forms/Select/CountrySelectControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useCustomerChangeProfileFormMeta } from 'components/Pages/Customer/EditProfile/customerChangeProfileFormMeta';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useFormContext } from 'react-hook-form';
import { CustomerChangeProfileFormType } from 'types/form';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { CompanyCustomer } from './CompanyCustomer';

export const BillingAddress: FC<{ companyCustomer: boolean }> = ({ companyCustomer }) => {
    const { t } = useTranslation();
    const { canManageCompanyData } = useAuthorization();
    const formProviderMethods = useFormContext<CustomerChangeProfileFormType>();
    const formMeta = useCustomerChangeProfileFormMeta();

    return (
        <FormBlockWrapper>
            <FormHeading>{t('Billing address')}</FormHeading>

            {companyCustomer && <CompanyCustomer />}

            <TextInputControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.street.name}
                textInputProps={{
                    label: formMeta.fields.street.label,
                    required: true,
                    type: 'text',
                    autoComplete: 'street-address',
                    disabled: !canManageCompanyData,
                }}
            />

            <FormColumn>
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.city.name}
                    width="wide"
                    textInputProps={{
                        label: formMeta.fields.city.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'address-level2',
                        disabled: !canManageCompanyData,
                    }}
                />
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.postcode.name}
                    width="narrow"
                    textInputProps={{
                        label: formMeta.fields.postcode.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'postal-code',
                        disabled: !canManageCompanyData,
                        inputMode: 'numeric',
                    }}
                />
            </FormColumn>

            <CountrySelectControlled
                formName={formMeta.formName}
                formProviderMethods={formProviderMethods}
                isDisabled={!canManageCompanyData}
                label={formMeta.fields.country.label}
                name={formMeta.fields.country.name}
            />
        </FormBlockWrapper>
    );
};
