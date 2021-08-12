import { FC, MouseEventHandler, ReactElement, useEffect, useRef } from 'react';
import {
    StyledClosePopupButton,
    StyledOverlay,
    StyledPopup,
    StyledPopupContent,
    StyledPopupHeader,
} from './Popup.style';
import { useShopsysDispatch, useShopsysSelector } from 'redux/store';
import NewsletterSuccess from './PopupContents/NewsletterSuccess/NewsletterSuccess';
import { popupActions } from 'redux/store/PopupStore';

/**
 * When a new content for the Popup component is created, a special type should be specified here.
 * This way we can switch over it in the renderPopupContent method
 */
export type PopupContentType = 'NewsletterSuccess';

/**
 * Popup component used for displaying any type of content above the main page content.
 */
const Popup: FC = () => {
    const dispatch = useShopsysDispatch();
    const { isPopupShown, popupContent } = useShopsysSelector((state) => state.popup);
    const onEscapeButtonPressHandler = useRef((event: KeyboardEvent): void => {
        if (event.key === 'Escape') {
            dispatch(popupActions.hidePopup());
        }
    }).current;

    useEffect(() => {
        if (isPopupShown) {
            document.addEventListener('keydown', onEscapeButtonPressHandler);
        } else {
            document.removeEventListener('keydown', onEscapeButtonPressHandler);
        }
    }, [isPopupShown]);

    const onClickCloseActionHandler: MouseEventHandler<HTMLElement> = () => {
        dispatch(popupActions.hidePopup());
    };

    if (isPopupShown) {
        return (
            <>
                <StyledOverlay onClick={onClickCloseActionHandler}></StyledOverlay>
                <StyledPopup role="dialog" aria-modal={true}>
                    <StyledPopupHeader>
                        <StyledClosePopupButton onClick={onClickCloseActionHandler}>
                            <img src="/svg/remove.svg" />
                        </StyledClosePopupButton>
                    </StyledPopupHeader>
                    <StyledPopupContent>{renderPopupContent(popupContent)}</StyledPopupContent>
                </StyledPopup>
            </>
        );
    }

    return null;
};

/**
 * When new type of the content is added, case in the switch statement below should be added as well
 * @param popupContent
 * @returns rendered Popup component content. This differs based on the popupContent parameter.
 */
export const renderPopupContent = (popupContent: PopupContentType | undefined): ReactElement | null => {
    switch (popupContent) {
        case 'NewsletterSuccess':
            return <NewsletterSuccess />;
        default:
            return null;
    }
};

/* @component */
export default Popup;
