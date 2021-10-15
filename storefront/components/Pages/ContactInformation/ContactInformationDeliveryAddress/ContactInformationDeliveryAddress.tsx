import {
    ContactInformationDeliveryAddressContentStyled,
    ContactInformationDeliveryAddressStyled,
} from './ContactInformationDeliveryAddress.style';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { FC, useRef, useState } from 'react';
import Checkbox from 'components/Forms/Checkbox';
import ChoiceFormLine from 'components/Forms/Lib/ChoiceFormLine';
import { CSSTransition } from 'react-transition-group';
import FormColumn from 'components/Forms/Lib/FormColumn';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import { getCountrySelectOptions } from 'pages/order/contact-information';
import Select from 'components/Forms/Select';
import TextInput from 'components/Forms/TextInput';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ContactInformationDeliveryAddress: FC = () => {
    const t = useTypedTranslationFunction();
    const contentElement = useRef<HTMLDivElement>(null);
    const cssTransitionRef = useRef<HTMLDivElement>(null);
    const [contentElementHeight, setContentElementHeight] = useState(0);
    const formProviderMethods = useFormContext();
    const deliveryAddressCheckbox = useWatch({ name: 'differentDeliveryAddress' });

    const calcHeight = () => {
        if (contentElement.current) {
            setContentElementHeight(contentElement.current.clientHeight);
        }
    };

    return (
        <>
            <FormLine lg="65%">
                <ChoiceFormLine>
                    <Controller
                        name="differentDeliveryAddress"
                        render={({ field }) => (
                            <Checkbox
                                fieldRef={field}
                                id="contactInformation_form-deliveryAddress"
                                label={t('Enter the delivery address')}
                            />
                        )}
                    />
                </ChoiceFormLine>
            </FormLine>

            <ContactInformationDeliveryAddressStyled contentElementHeight={contentElementHeight}>
                <CSSTransition
                    in={deliveryAddressCheckbox}
                    timeout={500}
                    classNames="contactInformationDeliveryAddress"
                    onEnter={calcHeight}
                    onExit={calcHeight}
                    unmountOnExit
                    nodeRef={cssTransitionRef}
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
                                <FormLine bottomGap={true} width="100%" lg="50%">
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
                            <FormLine bottomGap={true} lg="65%">
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
                            <FormLine bottomGap={true} lg="65%">
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
                            <FormLine bottomGap={true} lg="65%">
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
                            <FormColumn lg="65%">
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
                                <FormLine bottomGap={true} width="100%" lg="142px">
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
                            <FormLine lg="65%">
                                <Controller
                                    name="deliveryCountry"
                                    render={({ field }) => (
                                        <>
                                            <Select
                                                defaultValue={getCountrySelectOptions(t)[0]}
                                                options={getCountrySelectOptions(t)}
                                                onChange={(option: { value: string }) =>
                                                    formProviderMethods.setValue(field.name, option.value)
                                                }
                                            />
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
