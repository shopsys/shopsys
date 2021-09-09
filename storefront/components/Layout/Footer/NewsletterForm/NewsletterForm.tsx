import * as Yup from 'yup';
import {
    NewsletterFormButtonWrapper,
    NewsletterFormColumn,
    NewsletterFormInputWrapper,
    NewsletterFormWrapper,
} from './NewsletterForm.style';
import { FC } from 'react';
import Heading from '../../../Basic/Heading';
import { popupActions } from 'redux/store/PopupStore';
import ShopsysButton from '../../../Forms/ShopsysButton';
import ShopsysCheckbox from '../../../Forms/ShopsysCheckbox';
import ShopsysForm from '../../../Forms/ShopsysForm';
import ShopsysTextInput from '../../../Forms/ShopsysTextInput';
import { TFunction } from 'next-i18next';
import { useNewsletterSubscription } from '../../../../connectors/newsletter/Newsletter';
import { useShopsysDispatch } from 'redux/store';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';
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
    const dispatch = useShopsysDispatch();

    return (
        <NewsletterFormWrapper>
            <Heading type="h2">
                {t<string>('Sign up for our newsletter and get 35% discount on running apparel')}
            </Heading>
            <NewsletterFormColumn>
                <ShopsysForm
                    onSubmitHandler={subscribeToNewsletter}
                    onSuccessHandler={() => dispatch(popupActions.showPopup('NewsletterSuccess'))}
                    resolver={getNewsletterFormResolver(t)}
                >
                    <NewsletterFormInputWrapper>
                        <ShopsysTextInput
                            inputSize="small"
                            id="newsletter_form-email"
                            name="email"
                            label={t('email')}
                            required={true}
                        />
                        <NewsletterFormButtonWrapper>
                            <ShopsysButton type="submit" borderRadius="big">
                                {t<string>('Send')}
                            </ShopsysButton>
                        </NewsletterFormButtonWrapper>
                    </NewsletterFormInputWrapper>
                    <ShopsysCheckbox
                        id="newsletter_form-privacyPolicy"
                        name="privacyPolicy"
                        label={t('I take note of the processing of personal data')}
                        required={true}
                    />
                </ShopsysForm>
            </NewsletterFormColumn>
        </NewsletterFormWrapper>
    );
};

/* @component */
export default NewsletterForm;
