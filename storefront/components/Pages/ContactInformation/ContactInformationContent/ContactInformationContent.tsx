import {
    ContactInformationContentStyled,
    ContactInformationContentWrapperStyled,
} from './ContactInformationContent.style';
import { FC, useRef, useState } from 'react';
import ContactInformationAddress from 'components/Pages/ContactInformation/ContactInformationAddress';
import ContactInformationCompany from 'components/Pages/ContactInformation/ContactInformationCompany';
import ContactInformationUser from 'components/Pages/ContactInformation/ContactInformationUser';
import { Controller } from 'react-hook-form';
import { CSSTransition } from 'react-transition-group';
import Heading from 'components/Basic/Heading';
import Radiobutton from 'components/Forms/Radiobutton';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ContactInformationContent = {
    isEmailEntered: boolean;
    isCommonCustomerChecked: boolean;
    isCompanyCustomerChecked: boolean;
    onChangeValue: () => void;
};

const ContactInformationContent: FC<ContactInformationContent> = (props) => {
    const t = useTypedTranslationFunction();
    const contentElement = useRef<HTMLDivElement>(null);
    const nodeRef = useRef(null);
    const [contentElementHeight, setContentElementHeight] = useState(0);

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
                nodeRef={nodeRef}
            >
                <ContactInformationContentWrapperStyled ref={nodeRef}>
                    <div ref={contentElement}>
                        <Heading type="h3">{t('You will shop with us like')}</Heading>
                        <Controller
                            name="customer"
                            render={({ field }) => (
                                <div onChange={props.onChangeValue}>
                                    <Radiobutton
                                        name="customer"
                                        id="commonCustomer"
                                        value="commonCustomer"
                                        label={t('Private person')}
                                        fieldRef={field}
                                        checked={props.isCommonCustomerChecked}
                                    />

                                    <Radiobutton
                                        name="customer"
                                        id="companyCustomer"
                                        value="companyCustomer"
                                        label={t('Company')}
                                        fieldRef={field}
                                        checked={props.isCompanyCustomerChecked}
                                    />
                                </div>
                            )}
                        />

                        <ContactInformationUser />
                        {props.isCompanyCustomerChecked && <ContactInformationCompany />}
                        <ContactInformationAddress />
                    </div>
                </ContactInformationContentWrapperStyled>
            </CSSTransition>
        </ContactInformationContentStyled>
    );
};

/* @component */
export default ContactInformationContent;
