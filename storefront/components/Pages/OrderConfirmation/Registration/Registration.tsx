import { Controller, FormProvider } from 'react-hook-form';
import {
    RegistrationBenefitsListItem,
    RegistrationFormColumnStyled,
    RegistrationFormItemStyled,
    RegistrationFormStyled,
    RegistrationHeadingStyled,
    RegistrationMessageColumnStyled,
    RegistrationStyled,
} from './Registration.style';
import Button from 'components/Forms/Button';
import Checkbox from 'components/Forms/Checkbox';
import ChoiceFormLine from 'components/Forms/Lib/ChoiceFormLine';
import { FC } from 'react';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import TextInput from 'components/Forms/TextInput';
import { Trans } from 'react-i18next';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const Registration: FC = () => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useShopsysForm(undefined, { password: '', privacyPolicy: false });

    return (
        <Webline>
            <RegistrationStyled>
                <RegistrationMessageColumnStyled>
                    <RegistrationHeadingStyled type="h2">
                        <Trans i18nKey="Finish registration to loyalty program.">
                            Finish registration <br /> to
                            <strong>
                                loyalty <br /> program
                            </strong>
                        </Trans>
                    </RegistrationHeadingStyled>
                    <ul>
                        <RegistrationBenefitsListItem>
                            {t('You will have an overview of your orders and complaints')}
                        </RegistrationBenefitsListItem>
                        <RegistrationBenefitsListItem>
                            {t('Collecting points with every order')}
                        </RegistrationBenefitsListItem>
                        <RegistrationBenefitsListItem>
                            {t('Possibility of purchases for better prices')}
                        </RegistrationBenefitsListItem>
                        <RegistrationBenefitsListItem>
                            {t('Exclusive products as a part of the loyalty program')}
                        </RegistrationBenefitsListItem>
                    </ul>
                </RegistrationMessageColumnStyled>
                <RegistrationFormColumnStyled>
                    <RegistrationFormStyled>
                        <FormProvider {...formProviderMethods}>
                            <Controller
                                name="password"
                                render={({ field, fieldState: { error, invalid, isTouched } }) => (
                                    <RegistrationFormItemStyled>
                                        <FormLine>
                                            <TextInput
                                                name={field.name}
                                                label={t('Password')}
                                                type="password"
                                                fieldRef={field}
                                                required={true}
                                                isTouched={isTouched}
                                                hasError={invalid}
                                            />
                                            <FormLineError inputType="text-input" error={error} />
                                        </FormLine>
                                    </RegistrationFormItemStyled>
                                )}
                            />
                            <Controller
                                name="privacyPolicy"
                                render={({ field, fieldState: { error } }) => (
                                    <RegistrationFormItemStyled>
                                        <ChoiceFormLine>
                                            <Checkbox
                                                id={field.name}
                                                name={field.name}
                                                label={
                                                    <Trans i18nKey="I agree with terms and conditions and privacy policy">
                                                        I agree with
                                                        <a href="/">terms and conditions</a>
                                                        and privacy policy
                                                    </Trans>
                                                }
                                                fieldRef={field}
                                                required={true}
                                            />
                                            <FormLineError inputType="checkbox" error={error} />
                                        </ChoiceFormLine>
                                    </RegistrationFormItemStyled>
                                )}
                            />
                            <Button type="submit" variant="primary" borderRadius="big" style={{ width: '100%' }}>
                                {t('Create account')}
                            </Button>
                        </FormProvider>
                    </RegistrationFormStyled>
                </RegistrationFormColumnStyled>
            </RegistrationStyled>
        </Webline>
    );
};

export default Registration;
