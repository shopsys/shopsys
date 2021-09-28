import {
    ContactInformationContentSectionStyled,
    ContactInformationContentStyled,
    ContactInformationContentWrapperStyled,
} from './ContactInformationContent.style';
import { FC, useRef, useState } from 'react';
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
    const nodeRef = useRef(null);
    const [contentElementHeight, setContentElementHeight] = useState(0);
    const [isCompanyCustomerChecked, setIsCompanyCustomerChecked] = useState(false);
    const [isCommonCustomerChecked, setIsCommonCustomerChecked] = useState(true);

    const calcHeight = () => {
        if (contentElement.current) {
            setContentElementHeight(contentElement.current.clientHeight);
        }
    };

    const onChangeCustomerValue = () => {
        setIsCompanyCustomerChecked(!isCompanyCustomerChecked);
        setIsCommonCustomerChecked(!isCommonCustomerChecked);
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
                nodeRef={nodeRef}
            >
                <ContactInformationContentWrapperStyled ref={nodeRef}>
                    <div ref={contentElement}>
                        <ContactInformationContentSectionStyled>
                            <ContactInformationRegister />
                        </ContactInformationContentSectionStyled>

                        <ContactInformationContentSectionStyled>
                            <ContactInformationCustomer
                                isCompanyCustomerChecked={isCompanyCustomerChecked}
                                isCommonCustomerChecked={isCommonCustomerChecked}
                                onChangeCustomerValue={onChangeCustomerValue}
                            />
                        </ContactInformationContentSectionStyled>

                        <ContactInformationContentSectionStyled>
                            <ContactInformationUser />
                        </ContactInformationContentSectionStyled>

                        {isCompanyCustomerChecked && (
                            <ContactInformationContentSectionStyled>
                                <ContactInformationCompany />
                            </ContactInformationContentSectionStyled>
                        )}

                        <ContactInformationContentSectionStyled>
                            <ContactInformationAddress />
                        </ContactInformationContentSectionStyled>

                        <ContactInformationDeliveryAddress />
                    </div>
                </ContactInformationContentWrapperStyled>
            </CSSTransition>
        </ContactInformationContentStyled>
    );
};

/* @component */
export default ContactInformationContent;
