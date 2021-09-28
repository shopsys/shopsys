import {
    ContactInformationDeliveryAddressContentStyled,
    ContactInformationDeliveryAddressStyled,
} from './ContactInformationDeliveryAddress.style';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { FC, useRef, useState } from 'react';
import ChoiceFormLine from 'components/Forms/Lib/ChoiceFormLine';
import { CSSTransition } from 'react-transition-group';
import DeliveryCheckbox from './DeliveryCheckbox';
import FormColumn from 'components/Forms/Lib/FormColumn';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import TextInput from 'components/Forms/TextInput';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ContactInformationDeliveryAddress: FC = () => {
    const t = useTypedTranslationFunction();
    const contentElement = useRef<HTMLDivElement>(null);
    const nodeRef = useRef(null);
    const [contentElementHeight, setContentElementHeight] = useState(0);
    const [IsDeliveryAddressChecked, setIsDeliveryAddressChecked] = useState(false);

    const calcHeight = () => {
        if (contentElement.current) {
            setContentElementHeight(contentElement.current.clientHeight);
        }
    };

    return (
        <>
            <FormLine Lg="65%">
                <ChoiceFormLine>
                    <Controller
                        name="deliveryAddress"
                        render={({ field }) => (
                            <DeliveryCheckbox field={field} setIsDeliveryAddressChecked={setIsDeliveryAddressChecked} />
                        )}
                    />
                </ChoiceFormLine>
            </FormLine>

            <ContactInformationDeliveryAddressStyled contentElementHeight={contentElementHeight}>
                <CSSTransition
                    in={IsDeliveryAddressChecked}
                    timeout={500}
                    classNames="contactInformationDeliveryAddress"
                    onEnter={calcHeight}
                    onExit={calcHeight}
                    unmountOnExit
                    nodeRef={nodeRef}
                >
                    <div ref={cssTransitionRef}>
                        <ContactInformationDeliveryAddressContentStyled ref={contentElement}>
                            <FormColumn lg="65%">
                                <FormLine bottomGap={true} width="100%" lg="50%">
                                    <Controller
                                        name="deliveryFirstName"
                                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                            <>
                                                <TextInput
                                                    id="contactInformation_form-deliveryFirstName"
                                                    name="deliveryFirstName"
                                                    label={t('First name')}
                                                    required={true}
                                                    type="text"
                                                    isTouched={isTouched}
                                                    hasError={invalid}
                                                    fieldRef={field}
                                                />
                                                <FormLineError error={error} inputType="text-input" />
                                            </>
                                        )}
                                    />
                                </FormLine>
                                <FormLine bottomGap={true} Width="100%" Lg="50%">
                                    <Controller
                                        name="deliveryLastName"
                                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                            <>
                                                <TextInput
                                                    id="contactInformation_form-deliveryLastName"
                                                    name="deliveryLastName"
                                                    label={t('Last name')}
                                                    required={true}
                                                    type="text"
                                                    isTouched={isTouched}
                                                    hasError={invalid}
                                                    fieldRef={field}
                                                />
                                                <FormLineError error={error} inputType="text-input" />
                                            </>
                                        )}
                                    />
                                </FormLine>
                            </FormColumn>
                            <FormLine bottomGap={true} Lg="65%">
                                <Controller
                                    name="deliveryCompanyName"
                                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                        <>
                                            <TextInput
                                                id="contactInformation_form-deliveryCompanyName"
                                                name="deliveryCompanyName"
                                                label={t('Company')}
                                                type="text"
                                                isTouched={isTouched}
                                                hasError={invalid}
                                                fieldRef={field}
                                            />
                                            <FormLineError error={error} inputType="text-input" />
                                        </>
                                    )}
                                />
                            </FormLine>
                            <FormLine bottomGap={true} Lg="65%">
                                <Controller
                                    name="deliveryTelephone"
                                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                        <>
                                            <TextInput
                                                id="contactInformation_form-deliveryTelephone"
                                                name="deliveryTelephone"
                                                label={t('Telephone')}
                                                type="text"
                                                isTouched={isTouched}
                                                hasError={invalid}
                                                fieldRef={field}
                                            />
                                            <FormLineError error={error} inputType="text-input" />
                                        </>
                                    )}
                                />
                            </FormLine>
                            <FormLine bottomGap={true} Lg="65%">
                                <Controller
                                    name="deliveryStreet"
                                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                        <>
                                            <TextInput
                                                id="contactInformation_form-deliveryStreet"
                                                name="deliveryStreet"
                                                label={t('Street and house number')}
                                                type="text"
                                                required={true}
                                                isTouched={isTouched}
                                                hasError={invalid}
                                                fieldRef={field}
                                            />
                                            <FormLineError error={error} inputType="text-input" />
                                        </>
                                    )}
                                />
                            </FormLine>
                            <FormColumn Lg="65%">
                                <FormLine bottomGap={true}>
                                    <Controller
                                        name="deliveryCity"
                                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                            <>
                                                <TextInput
                                                    id="contactInformation_form-deliveryCity"
                                                    name="deliveryCity"
                                                    label={t('City')}
                                                    required={true}
                                                    type="text"
                                                    isTouched={isTouched}
                                                    hasError={invalid}
                                                    fieldRef={field}
                                                />
                                                <FormLineError error={error} inputType="text-input" />
                                            </>
                                        )}
                                    />
                                </FormLine>
                                <FormLine bottomGap={true} Width="100%" Lg="142px">
                                    <Controller
                                        name="deliveryPostcode"
                                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                            <>
                                                <TextInput
                                                    id="contactInformation_form-deliveryPostcode"
                                                    name="deliveryPostcode"
                                                    label={t('Postcode')}
                                                    required={true}
                                                    type="text"
                                                    isTouched={isTouched}
                                                    hasError={invalid}
                                                    fieldRef={field}
                                                />
                                                <FormLineError error={error} inputType="text-input" />
                                            </>
                                        )}
                                    />
                                </FormLine>
                            </FormColumn>
                            <FormLine Lg="65%">
                                <Controller
                                    name="deliveryCountry"
                                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                        <>
                                            <select name="deliveryCountry" id="contactInformation_form-deliveryCountry">
                                                <option value="Slovensko">Slovensko</option>
                                                <option value="Česká republika">Česká republika</option>
                                            </select>
                                        </>
                                    )}
                                />
                            </FormLine>
                        </ContactInformationDeliveryAddressContentStyled>
                    </div>
                </CSSTransition>
            </ContactInformationDeliveryAddressStyled>
        </>
    );
};

/* @component */
export default ContactInformationDeliveryAddress;
