import {
    ContactInformationContentSectionStyled,
    ContactInformationContentStyled,
} from './ContactInformationContent.style';
import {
    ContactInformationFormType,
    useContactInformationFormMeta,
} from 'components/Pages/Order/ContactInformation/formMeta';
import { FC, useRef, useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import ContactInformationAddress from 'components/Pages/Order/ContactInformation/ContactInformationAddress';
import ContactInformationCompany from 'components/Pages/Order/ContactInformation/ContactInformationCompany';
import ContactInformationCustomer from 'components/Pages/Order/ContactInformation/ContactInformationCustomer';
import ContactInformationDeliveryAddress from 'components/Pages/Order/ContactInformation/ContactInformationDeliveryAddress';
import ContactInformationRegister from 'components/Pages/Order/ContactInformation/ContactInformationRegister';
import ContactInformationUser from 'components/Pages/Order/ContactInformation/ContactInformationUser';
import { CSSTransition } from 'react-transition-group';

type ContactInformationContent = {
    isEmailEntered: boolean;
};

const ContactInformationContent: FC<ContactInformationContent> = (props) => {
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
                            <ContactInformationRegister />
                        </ContactInformationContentSectionStyled>

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
                    </div>
                </div>
            </CSSTransition>
        </ContactInformationContentStyled>
    );
};

/* @component */
export default ContactInformationContent;
