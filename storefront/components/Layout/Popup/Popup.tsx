import { PopupButtonCloseStyled, PopupContentStyled, PopupHeaderStyled, PopupStyled } from './Popup.style';
import { Icon } from 'components/Basic/Icon/Icon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { Portal } from 'components/Basic/Portal/Portal';
import { canUseDom } from 'helpers/misc/canUseDom';
import { FC, MouseEventHandler, useEffect, useRef } from 'react';
import { AnyStyledComponent } from 'styled-components';

type PopupProps = {
    isVisible: boolean;
    onCloseCallback: () => void;
    wrapperComponent?: AnyStyledComponent;
    hideCloseButton?: boolean;
    className?: string;
};

const TEST_IDENTIFIER = 'layout-popup';

export const Popup: FC<PopupProps> = ({
    isVisible,
    onCloseCallback,
    children,
    hideCloseButton,
    wrapperComponent,
    className,
}) => {
    const onEscapeButtonPressHandler = useRef((event: KeyboardEvent): void => {
        if (event.key === 'Escape') {
            onCloseCallback();
        }
    }).current;

    useEffect(() => {
        if (!canUseDom()) {
            return undefined;
        }

        if (isVisible) {
            document.addEventListener('keydown', onEscapeButtonPressHandler);
        } else {
            document.removeEventListener('keydown', onEscapeButtonPressHandler);
        }

        return () => document.removeEventListener('keydown', onEscapeButtonPressHandler);
    }, [onEscapeButtonPressHandler, isVisible]);

    const onClickCloseActionHandler: MouseEventHandler<HTMLElement> = () => {
        onCloseCallback();
    };

    const PopupWrapper = wrapperComponent !== undefined ? wrapperComponent : PopupStyled;

    if (!isVisible) {
        return null;
    }

    return (
        <Portal>
            <Overlay $isActive={isVisible} onClick={onClickCloseActionHandler}></Overlay>
            <PopupWrapper role="dialog" aria-modal data-testid={TEST_IDENTIFIER} className={className}>
                {hideCloseButton !== true && (
                    <PopupHeaderStyled>
                        <PopupButtonCloseStyled type="button" onClick={onClickCloseActionHandler}>
                            <Icon iconType="icon" icon="Remove" width={24} height={24} className="text-primary" />
                        </PopupButtonCloseStyled>
                    </PopupHeaderStyled>
                )}
                <PopupContentStyled>{children}</PopupContentStyled>
            </PopupWrapper>
        </Portal>
    );
};
