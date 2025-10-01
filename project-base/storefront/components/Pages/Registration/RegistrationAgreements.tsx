import { useRegistrationFormMeta } from './registrationFormMeta';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { FormBlockAgreements } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { useFormContext } from 'react-hook-form';
import { RegistrationFormType } from 'types/form';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const RegistrationAgreements: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const formMeta = useRegistrationFormMeta(formProviderMethods);

    return (
        <FormBlockAgreements>
            <legend className="sr-only">{t('Privacy policy')}</legend>

            <CheckboxControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.newsletterSubscription.name}
                render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                checkboxProps={{
                    label: formMeta.fields.newsletterSubscription.label,
                }}
            />

            <CheckboxControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.gdprAgreement.name}
                render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                checkboxProps={{
                    label: formMeta.fields.gdprAgreement.label,
                    required: true,
                }}
            />
        </FormBlockAgreements>
    );
};
