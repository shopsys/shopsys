import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { FormBlockAgreements } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { useCustomerChangeProfileFormMeta } from 'components/Pages/Customer/EditProfile/customerChangeProfileFormMeta';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useFormContext } from 'react-hook-form';
import { CustomerChangeProfileFormType } from 'types/form';

export const NewsletterSubscription: FC = () => {
    const formProviderMethods = useFormContext<CustomerChangeProfileFormType>();
    const formMeta = useCustomerChangeProfileFormMeta();
    const { canManagePersonalData } = useAuthorization();

    return (
        <FormBlockAgreements>
            <CheckboxControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.newsletterSubscription.name}
                render={(checkbox) => <FormLine>{checkbox}</FormLine>}
                checkboxProps={{
                    label: formMeta.fields.newsletterSubscription.label,
                    disabled: !canManagePersonalData,
                }}
            />
        </FormBlockAgreements>
    );
};
