import { FC, MouseEventHandler, useEffect, useRef } from 'react';
import {
    PopupButtonCloseIconStyled,
    PopupButtonCloseStyled,
    PopupContentStyled,
    PopupHeaderStyled,
    PopupStyled,
} from './Popup.style';
import { AnyStyledComponent } from 'styled-components';
import { canUseDom } from 'helpers/canUseDom';
import Overlay from 'components/Basic/Overlay';
import Portal from 'components/Basic/Portal';

type PopupProps = {
    isVisible: boolean;
    onCloseCallback: () => void;
    wrapperComponent?: AnyStyledComponent;
    hideCloseButton?: boolean;
};

/**
 * Popup component used for displaying any type of content above the main page content.
 */
const Popup: FC<PopupProps> = (props) => {
    const testIdentifier = 'layout-popup';

    const onEscapeButtonPressHandler = useRef((event: KeyboardEvent): void => {
        if (event.key === 'Escape') {
            props.onCloseCallback();
        }
    }).current;

    useEffect(() => {
        if (!canUseDom()) {
            return undefined;
        }

        if (props.isVisible) {
            document.addEventListener('keydown', onEscapeButtonPressHandler);
        } else {
            document.removeEventListener('keydown', onEscapeButtonPressHandler);
        }

        return () => document.removeEventListener('keydown', onEscapeButtonPressHandler);
    }, [onEscapeButtonPressHandler, props.isVisible]);

    const onClickCloseActionHandler: MouseEventHandler<HTMLElement> = () => {
        props.onCloseCallback();
    };

    const PopupWrapper = props.wrapperComponent !== undefined ? props.wrapperComponent : PopupStyled;

    if (props.isVisible) {
        return (
            <Portal>
                <Overlay onClick={onClickCloseActionHandler}></Overlay>
                <PopupWrapper role="dialog" aria-modal={true} data-testid={testIdentifier}>
                    {props.hideCloseButton !== true && (
                        <PopupHeaderStyled>
                            <PopupButtonCloseStyled type="button" onClick={onClickCloseActionHandler}>
                                <PopupButtonCloseIconStyled iconType="icon" icon="Remove" />
                            </PopupButtonCloseStyled>
                        </PopupHeaderStyled>
                    )}
                    <PopupContentStyled>{props.children}</PopupContentStyled>
                </PopupWrapper>
            </Portal>
        );
    }

    return null;
};

/* @component */
export default Popup;
