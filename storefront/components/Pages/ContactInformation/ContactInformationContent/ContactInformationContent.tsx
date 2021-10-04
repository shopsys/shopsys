import {
    ContactInformationContentSectionStyled,
    ContactInformationContentStyled,
} from './ContactInformationContent.style';
import { FC, useRef, useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import ContactInformationAddress from 'components/Pages/ContactInformation/ContactInformationAddress';
import ContactInformationCompany from 'components/Pages/ContactInformation/ContactInformationCompany';
import ContactInformationCustomer from 'components/Pages/ContactInformation/ContactInformationCustomer';
import ContactInformationDeliveryAddress from 'components/Pages/ContactInformation/ContactInformationDeliveryAddress';
import ContactInformationRegister from 'components/Pages/ContactInformation/ContactInformationRegister';
import ContactInformationUser from 'components/Pages/ContactInformation/ContactInformationUser';
import { CSSTransition } from 'react-transition-group';

type ContactInformationContent = {
    isEmailEntered: boolean;
};

const ContactInformationContent: FC<ContactInformationContent> = (props) => {
    const contentElement = useRef<HTMLDivElement>(null);
    const cssTransitionRef = useRef<HTMLDivElement>(null);
    const [contentElementHeight, setContentElementHeight] = useState(0);
    const { control } = useFormContext();
    const customer = useWatch({ name: 'customer', control });

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
                            <ContactInformationCustomer currentValue={customer} />
                        </ContactInformationContentSectionStyled>

                        <ContactInformationContentSectionStyled>
                            <ContactInformationUser />
                        </ContactInformationContentSectionStyled>

                        {customer === 'companyCustomer' && (
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
