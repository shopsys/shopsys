import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { FormHeading, FormBlockWrapper } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useCustomerChangeProfileFormMeta } from 'components/Pages/Customer/customerChangeProfileFormMeta';
import useTranslation from 'next-translate/useTranslation';
import { useFormContext } from 'react-hook-form';
import { CustomerChangeProfileFormType } from 'types/form';

export const PersonalData: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<CustomerChangeProfileFormType>();
    const formMeta = useCustomerChangeProfileFormMeta(formProviderMethods);

    return (
        <FormBlockWrapper>
            <FormHeading>{t('Personal data')}</FormHeading>
            <TextInputControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.email.name}
                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                textInputProps={{
                    label: formMeta.fields.email.label,
                    required: false,
                    disabled: true,
                    type: 'email',
                    autoComplete: 'email',
                }}
            />
            <FormLine bottomGap>
                <div>
                    {t(
                        'To prevent the possibility of account theft, it is necessary to deal with the change of e-mail individually. If your e-mail address has changed, please contact us.',
                    )}
                </div>
            </FormLine>
            <FormColumn>
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.firstName.name}
                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                    textInputProps={{
                        label: formMeta.fields.firstName.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'given-name',
                    }}
                />
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.lastName.name}
                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                    textInputProps={{
                        label: formMeta.fields.lastName.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'family-name',
                    }}
                />
            </FormColumn>
            <TextInputControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.telephone.name}
                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                textInputProps={{
                    label: formMeta.fields.telephone.label,
                    required: true,
                    type: 'tel',
                    autoComplete: 'tel',
                }}
            />
            <CheckboxControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.newsletterSubscription.name}
                render={(checkbox) => <FormLine>{checkbox}</FormLine>}
                checkboxProps={{
                    label: formMeta.fields.newsletterSubscription.label,
                }}
            />
        </FormBlockWrapper>
    );
};
