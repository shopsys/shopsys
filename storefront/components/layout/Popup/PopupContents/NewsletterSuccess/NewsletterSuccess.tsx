import { popupActions } from 'redux/store/PopupStore';
import { ReactElement } from 'react';
import ShopsysButton from 'components/forms/ShopsysButton';
import ShopsysHeading from 'components/basic/ShopsysHeading';
import { StyledNewsletterSuccess } from './NewsletterSuccess.style';
import { useShopsysDispatch } from 'redux/store';
import { useTranslation } from 'next-i18next';

/**
 * A content for the Popup component which is displayed when the user successfully subscribes to newlsetter
 */
function NewsletterSuccess(): ReactElement {
    const { t } = useTranslation();
    const dispatch = useShopsysDispatch();

    const onCloseButtonClickHandler = () => {
        dispatch(popupActions.hidePopup());
    };

    return (
        <StyledNewsletterSuccess>
            <ShopsysHeading type="h3">{t<string>('You have successfully subscribed to our newsletter')}</ShopsysHeading>
            <div>
                <ShopsysButton onClick={onCloseButtonClickHandler}>{t<string>('Close')}</ShopsysButton>
            </div>
        </StyledNewsletterSuccess>
    );
}

export default NewsletterSuccess;
