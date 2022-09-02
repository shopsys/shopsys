import {
    ContactInformationContentSectionStyled,
    ContactInformationContentStyled,
} from './ContactInformationContent.style';
import Heading from 'components/Basic/Heading';
import FormLine from 'components/Forms/Lib/FormLine';
import Textarea from 'components/Forms/Textarea';
import ContactInformationAddress from 'components/Pages/Order/ContactInformation/ContactInformationAddress';
import ContactInformationCompany from 'components/Pages/Order/ContactInformation/ContactInformationCompany';
import ContactInformationCustomer from 'components/Pages/Order/ContactInformation/ContactInformationCustomer';
import ContactInformationDeliveryAddress from 'components/Pages/Order/ContactInformation/ContactInformationDeliveryAddress';
import ContactInformationUser from 'components/Pages/Order/ContactInformation/ContactInformationUser';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/formMeta';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, useRef, useState } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { CSSTransition } from 'react-transition-group';
import { ContactInformationFormType } from 'types/form';

type ContactInformationContentProps = {
    isEmailEntered: boolean;
};

const ContactInformationContent: FC<ContactInformationContentProps> = (props) => {
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
        <ContactInformationContentStyled contentElementHeight={contentElementHeight}>
            <CSSTransition
                in={props.isEmailEntered}
                timeout={500}
                classNames="contactInformationContent"
                onEnter={calcHeight}
                onExit={calcHeight}
                unmountOnExit
                nodeRef={cssTransitionRef}
            >
                <div ref={cssTransitionRef}>
                    <div ref={contentElement}>
                        <ContactInformationContentSectionStyled>
                            <ContactInformationCustomer />
                        </ContactInformationContentSectionStyled>

                        <ContactInformationContentSectionStyled>
                            <ContactInformationUser />
                        </ContactInformationContentSectionStyled>

                        {customerValue === 'companyCustomer' && (
                            <ContactInformationContentSectionStyled>
                                <ContactInformationCompany />
                            </ContactInformationContentSectionStyled>
                        )}

                        <ContactInformationContentSectionStyled>
                            <ContactInformationAddress />
                        </ContactInformationContentSectionStyled>

                        <ContactInformationDeliveryAddress />

                        <Heading type="h3">{t('Note')}</Heading>
                        <FormLine bottomGap={true} lg="65%">
                            <Controller
                                name={formMeta.fields.note.name}
                                render={({ field, fieldState: { isTouched, invalid } }) => (
                                    <Textarea
                                        id={formMeta.formName + '-' + formMeta.fields.note.name}
                                        name={formMeta.fields.note.name}
                                        label={formMeta.fields.note.label}
                                        fieldRef={field}
                                        isTouched={isTouched}
                                        hasError={invalid}
                                        rows={3}
                                    />
                                )}
                            />
                        </FormLine>
                    </div>
                </div>
            </CSSTransition>
        </ContactInformationContentStyled>
    );
};

export default ContactInformationContent;
