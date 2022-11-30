import {
    ContactInformationFormWrapperSectionStyled,
    ContactInformationFormWrapperStyled,
} from './ContactInformationFormWrapper.style';
import { Heading } from 'components/Basic/Heading/Heading';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { TextareaControlled } from 'components/Forms/Textarea/TextareaControlled';
import { ContactInformationAddress } from 'components/Pages/Order/ContactInformation/ContactInformationAddress/ContactInformationAddress';
import { ContactInformationCompany } from 'components/Pages/Order/ContactInformation/ContactInformationCompany/ContactInformationCompany';
import { ContactInformationCustomer } from 'components/Pages/Order/ContactInformation/ContactInformationCustomer/ContactInformationCustomer';
import { ContactInformationDeliveryAddress } from 'components/Pages/Order/ContactInformation/ContactInformationDeliveryAddress/ContactInformationDeliveryAddress';
import { ContactInformationUser } from 'components/Pages/Order/ContactInformation/ContactInformationUser/ContactInformationUser';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/formMeta';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useRef, useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { CSSTransition } from 'react-transition-group';
import { ContactInformationFormType } from 'types/form';

type ContactInformationFormWrapperProps = {
    isEmailEntered: boolean;
};

export const ContactInformationFormWrapper: FC<ContactInformationFormWrapperProps> = ({ isEmailEntered }) => {
    const t = useTypedTranslationFunction();
    const contentElement = useRef<HTMLDivElement>(null);
    const cssTransitionRef = useRef<HTMLDivElement>(null);
    const [contentElementHeight, setContentElementHeight] = useState(0);
    const formProviderMethods = useFormContext<ContactInformationFormType>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const customerValue = useWatch({ name: formMeta.fields.customer.name, control: formProviderMethods.control });

    const calcHeight = () => {
        if (contentElement.current) {
            setContentElementHeight(contentElement.current.clientHeight);
        }
    };

    return (
        <ContactInformationFormWrapperStyled contentElementHeight={contentElementHeight}>
            <CSSTransition
                in={isEmailEntered}
                timeout={500}
                classNames="contactInformationFormWrapper"
                onEnter={calcHeight}
                onExit={calcHeight}
                unmountOnExit
                nodeRef={cssTransitionRef}
            >
                <div ref={cssTransitionRef}>
                    <div ref={contentElement}>
                        <ContactInformationFormWrapperSectionStyled>
                            <ContactInformationCustomer />
                        </ContactInformationFormWrapperSectionStyled>

                        <ContactInformationFormWrapperSectionStyled>
                            <ContactInformationUser />
                        </ContactInformationFormWrapperSectionStyled>

                        {customerValue === 'companyCustomer' && (
                            <ContactInformationFormWrapperSectionStyled>
                                <ContactInformationCompany />
                            </ContactInformationFormWrapperSectionStyled>
                        )}

                        <ContactInformationFormWrapperSectionStyled>
                            <ContactInformationAddress />
                        </ContactInformationFormWrapperSectionStyled>

                        <ContactInformationDeliveryAddress />

                        <Heading type="h3">{t('Note')}</Heading>
                        <TextareaControlled
                            name={formMeta.fields.note.name}
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            render={(textarea) => (
                                <FormLine bottomGap lg="65%">
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
            </CSSTransition>
        </ContactInformationFormWrapperStyled>
    );
};
