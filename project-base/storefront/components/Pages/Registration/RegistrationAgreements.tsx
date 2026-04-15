import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { FormBlockAgreements } from 'components/Forms/Form/Form';
import { useFormContext } from 'react-hook-form';
import { RegistrationFormType } from 'types/form';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useRegistrationFormMeta } from './registrationFormMeta';

export const RegistrationAgreements: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const formMeta = useRegistrationFormMeta();

    return (
        <FormBlockAgreements>
            <legend className="sr-only">{t('Privacy policy')}</legend>

            <CheckboxControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.newsletterSubscription.name}
                checkboxProps={{
                    label: formMeta.fields.newsletterSubscription.label,
                }}
            />

            <CheckboxControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.gdprAgreement.name}
                checkboxProps={{
                    label: formMeta.fields.gdprAgreement.label,
                    required: true,
                }}
            />
        </FormBlockAgreements>
    );
};
