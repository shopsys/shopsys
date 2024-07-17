import { FormBlockWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextareaControlled } from 'components/Forms/Textarea/TextareaControlled';
import { ContactInformationAddress } from 'components/Pages/Order/ContactInformation/FormBlocks/ContactInformationAddress';
import { ContactInformationCompany } from 'components/Pages/Order/ContactInformation/FormBlocks/ContactInformationCompany';
import { ContactInformationCustomer } from 'components/Pages/Order/ContactInformation/FormBlocks/ContactInformationCustomer';
import { ContactInformationDeliveryAddress } from 'components/Pages/Order/ContactInformation/FormBlocks/ContactInformationDeliveryAddress';
import { ContactInformationUser } from 'components/Pages/Order/ContactInformation/FormBlocks/ContactInformationUser';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import useTranslation from 'next-translate/useTranslation';
import { useRef } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';

export const ContactInformationFormContent: FC = () => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const { t } = useTranslation();
    const contentElement = useRef<HTMLDivElement>(null);
    const cssTransitionRef = useRef<HTMLDivElement>(null);
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const customerValue = useWatch({ name: formMeta.fields.customer.name, control: formProviderMethods.control });

    return (
        <div className="overflow-hidden transition-all" ref={cssTransitionRef}>
            <div ref={contentElement}>
                <ContactInformationCustomer />

                <ContactInformationUser />

                {customerValue === 'companyCustomer' && <ContactInformationCompany />}

                <ContactInformationAddress />

                <ContactInformationDeliveryAddress />

                <FormBlockWrapper>
                    <FormHeading>{t('Note')}</FormHeading>
                    <TextareaControlled
                        control={formProviderMethods.control}
                        formName={formMeta.formName}
                        name={formMeta.fields.note.name}
                        render={(textarea) => <FormLine>{textarea}</FormLine>}
                        textareaProps={{
                            label: formMeta.fields.note.label,
                            rows: 3,
                            onChange: (event) => updateContactInformation({ note: event.currentTarget.value }),
                        }}
                    />
                </FormBlockWrapper>
            </div>
        </div>
    );
};
