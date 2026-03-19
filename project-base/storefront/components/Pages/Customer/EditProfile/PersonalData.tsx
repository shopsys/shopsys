import { FormBlockWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { PhoneNumberInputControlled } from 'components/Forms/PhonePrefixSelect/PhoneNumberInputControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useCustomerChangeProfileFormMeta } from 'components/Pages/Customer/EditProfile/customerChangeProfileFormMeta';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useFormContext } from 'react-hook-form';
import { CustomerChangeProfileFormType } from 'types/form';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const PersonalData: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<CustomerChangeProfileFormType>();
    const formMeta = useCustomerChangeProfileFormMeta();
    const { canManagePersonalData } = useAuthorization();

    return (
        <FormBlockWrapper>
            <FormHeading>{t('Personal data')}</FormHeading>

            <TextInputControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.email.name}
                textInputProps={{
                    label: formMeta.fields.email.label,
                    required: false,
                    disabled: true,
                    type: 'email',
                    autoComplete: 'email',
                }}
            />

            <p className="text-sm">
                {t(
                    'To prevent the possibility of account theft, it is necessary to deal with the change of e-mail individually. If your e-mail address has changed, please contact us.',
                )}
            </p>

            <FormColumn>
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.firstName.name}
                    width="half"
                    textInputProps={{
                        label: formMeta.fields.firstName.label,
                        required: true,
                        disabled: !canManagePersonalData,
                        type: 'text',
                        autoComplete: 'given-name',
                    }}
                />
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.lastName.name}
                    width="half"
                    textInputProps={{
                        label: formMeta.fields.lastName.label,
                        required: true,
                        disabled: !canManagePersonalData,
                        type: 'text',
                        autoComplete: 'family-name',
                    }}
                />
            </FormColumn>

            <PhoneNumberInputControlled
                formName={formMeta.formName}
                formProviderMethods={formProviderMethods}
                isDisabled={!canManagePersonalData}
                prefixCountryCodeName={formMeta.fields.telephonePrefixCountryCode.name}
                prefixName={formMeta.fields.telephonePrefix.name}
                telephoneLabel={formMeta.fields.telephone.label}
                telephoneName={formMeta.fields.telephone.name}
            />
        </FormBlockWrapper>
    );
};
