import * as Yup from 'yup';
import {
    NewsletterFormButtonWrapperStyled,
    NewsletterFormColumnStyled,
    NewsletterFormInputWrapperStyled,
    NewsletterFormWrapperStyled,
} from './NewsletterForm.style';
import Button from 'components/Forms/Button';
import Checkbox from 'components/Forms/Checkbox';
import ChoiceFormLine from 'components/Forms/Lib/ChoiceFormLine';
import { Controller } from 'react-hook-form';
import { FC } from 'react';
import Form from 'components/Forms/Form';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import Heading from 'components/Basic/Heading';
import { showSuccessMessage } from 'components/Helpers/Toasts';
import TextInput from 'components/Forms/TextInput';
import { TFunction } from 'next-i18next';
import { useNewsletterSubscription } from 'connectors/newsletter/Newsletter';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { yupResolver } from '@hookform/resolvers/yup';

const getNewsletterFormResolver = (t: TFunction) => {
    return yupResolver(
        Yup.object().shape({
            email: Yup.string().required(t('This field is required')).email(t('This value is not a valid email')),
            privacyPolicy: Yup.bool().oneOf([true], t('You have to agree with our privacy policy')),
        }),
    );
};

/**
 * Newsletter form block, which is displayed in the Footer section and serves as
 * a signup form for the Newsletter.
 */
const NewsletterForm: FC = () => {
    const t = useTypedTranslationFunction();
    const [, subscribeToNewsletter] = useNewsletterSubscription();

    return (
        <NewsletterFormWrapperStyled>
            <Heading type="h2">{t('Sign up for our newsletter and get 35% discount on running apparel')}</Heading>
            <NewsletterFormColumnStyled>
                <Form
                    onSubmitHandler={subscribeToNewsletter}
                    onSuccessHandler={() => showSuccessMessage(t('You have successfully subscribed to our newsletter'))}
                    resolver={getNewsletterFormResolver(t)}
                    defaultValues={{ email: '', privacyPolicy: false }}
                >
                    <NewsletterFormInputWrapperStyled>
                        <FormLine>
                            <Controller
                                name="email"
                                render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                    <>
                                        <TextInput
                                            id="newsletter_form-email"
                                            name="email"
                                            label={t('email')}
                                            required={true}
                                            type="text"
                                            inputSize="small"
                                            isTouched={isTouched}
                                            hasError={invalid}
                                            fieldRef={field}
                                        />
                                        <FormLineError textInputSize="small" error={error} inputType="text-input" />
                                    </>
                                )}
                            />
                        </FormLine>
                        <NewsletterFormButtonWrapperStyled>
                            <Button type="submit" borderRadius="big">
                                {t('Send')}
                            </Button>
                        </NewsletterFormButtonWrapperStyled>
                    </NewsletterFormInputWrapperStyled>
                    <ChoiceFormLine>
                        <Controller
                            name="privacyPolicy"
                            render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                <>
                                    <Checkbox
                                        id="newsletter_form-privacyPolicy"
                                        name={field.name}
                                        label={t('I take note of the processing of personal data')}
                                        required={true}
                                        isTouched={isTouched}
                                        hasError={invalid}
                                        fieldRef={field}
                                    />
                                    <FormLineError error={error} inputType="checkbox" />
                                </>
                            )}
                        />
                    </ChoiceFormLine>
                </Form>
            </NewsletterFormColumnStyled>
        </NewsletterFormWrapperStyled>
    );
};

/* @component */
export default NewsletterForm;
