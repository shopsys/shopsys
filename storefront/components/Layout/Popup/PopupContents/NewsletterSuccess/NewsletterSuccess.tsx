import Button from 'components/Forms/Button';
import Heading from 'components/Basic/Heading';
import { popupActions } from 'redux/store/PopupStore';
import { ReactElement } from 'react';
import { StyledNewsletterSuccess } from './NewsletterSuccess.style';
import { useShopsysDispatch } from 'redux/store';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

/**
 * A content for the Popup component which is displayed when the user successfully subscribes to newlsetter
 */
function NewsletterSuccess(): ReactElement {
    const t = useTypedTranslationFunction();
    const dispatch = useShopsysDispatch();

    const onCloseButtonClickHandler = () => {
        dispatch(popupActions.hidePopup());
    };
    return (
        <StyledNewsletterSuccess>
            <Heading type="h3">{t('You have successfully subscribed to our newsletter')}</Heading>
            <div>
                <Button type="button" onClick={onCloseButtonClickHandler}>
                    {t('Close')}
                </Button>
            </div>
        </StyledNewsletterSuccess>
    );
}

export default NewsletterSuccess;
