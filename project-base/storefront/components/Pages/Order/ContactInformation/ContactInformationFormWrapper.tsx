import { Heading } from 'components/Basic/Heading/Heading';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextareaControlled } from 'components/Forms/Textarea/TextareaControlled';
import { ContactInformationAddress } from 'components/Pages/Order/ContactInformation/ContactInformationAddress';
import { ContactInformationCompany } from 'components/Pages/Order/ContactInformation/ContactInformationCompany';
import { ContactInformationCustomer } from 'components/Pages/Order/ContactInformation/ContactInformationCustomer';
import { ContactInformationDeliveryAddress } from 'components/Pages/Order/ContactInformation/ContactInformationDeliveryAddress';
import { ContactInformationUser } from 'components/Pages/Order/ContactInformation/ContactInformationUser';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/formMeta';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRef } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/zustand/slices/createContactInformationSlice';

export const ContactInformationFormWrapper: FC = () => {
    const t = useTypedTranslationFunction();
    const contentElement = useRef<HTMLDivElement>(null);
    const cssTransitionRef = useRef<HTMLDivElement>(null);
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const customerValue = useWatch({ name: formMeta.fields.customer.name, control: formProviderMethods.control });

    return (
        <div className="overflow-hidden transition-all" ref={cssTransitionRef}>
            <div ref={contentElement}>
                <div className="mb-10">
                    <ContactInformationCustomer />
                </div>

                <div className="mb-10">
                    <ContactInformationUser />
                </div>

                {customerValue === 'companyCustomer' && (
                    <div className="mb-10">
                        <ContactInformationCompany />
                    </div>
                )}

                <div className="mb-10">
                    <ContactInformationAddress />
                </div>

                <ContactInformationDeliveryAddress />

                <Heading type="h3">{t('Note')}</Heading>
                <TextareaControlled
                    name={formMeta.fields.note.name}
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    render={(textarea) => (
                        <FormLine bottomGap className="flex-none lg:w-[65%]">
                            {textarea}
                        </FormLine>
                    )}
                    textareaProps={{
                        label: formMeta.fields.note.label,
                        rows: 3,
                    }}
                />
            </div>
        </div>
    );
};
