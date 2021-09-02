import { FC, MouseEventHandler, useEffect, useRef } from 'react';
import {
    PopupButtonCloseIconStyled,
    PopupButtonCloseStyled,
    PopupContentStyled,
    PopupHeaderStyled,
    PopupStyled,
} from './Popup.style';
import { AnyStyledComponent } from 'styled-components';
import { useShopsysDispatch, useShopsysSelector } from 'redux/store';
import NewsletterSuccess from './PopupContents/NewsletterSuccess/NewsletterSuccess';
import { popupActions } from 'redux/store/PopupStore';
import ShopsysOverlay from '../../basic/ShopsysOverlay';

type PopupProps = {
    isVisible: boolean;
    onCloseCallback: () => void;
    wrapperComponent?: AnyStyledComponent;
};

/**
 * Popup component used for displaying any type of content above the main page content.
 */
const Popup: FC<PopupProps> = (props) => {
    const onEscapeButtonPressHandler = useRef((event: KeyboardEvent): void => {
        if (event.key === 'Escape') {
            props.onCloseCallback();
        }
    }).current;

    useEffect(() => {
        if (props.isVisible) {
            document.addEventListener('keydown', onEscapeButtonPressHandler);
        } else {
            document.removeEventListener('keydown', onEscapeButtonPressHandler);
        }
    }, [props.isVisible]);

    const onClickCloseActionHandler: MouseEventHandler<HTMLElement> = () => {
        props.onCloseCallback();
    };

    const PopupWrapper = props.wrapperComponent !== undefined ? props.wrapperComponent : PopupStyled;

    if (props.isVisible) {
        return (
            <>
                <OverlayStyled onClick={onClickCloseActionHandler}></OverlayStyled>
                <PopupWrapper role="dialog" aria-modal={true}>
                    <PopupHeaderStyled>
                        <PopupButtonCloseStyled type="button" onClick={onClickCloseActionHandler}>
                            <PopupButtonCloseIconStyled icon="Remove" />
                        </PopupButtonCloseStyled>
                    </PopupHeaderStyled>
                    <PopupContentStyled>{props.children}</PopupContentStyled>
                </PopupWrapper>
            </>
        );
    }

    return null;
};

/* @component */
export default Popup;
