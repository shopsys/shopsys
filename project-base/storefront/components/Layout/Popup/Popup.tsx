import { Icon } from 'components/Basic/Icon/Icon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { Portal } from 'components/Basic/Portal/Portal';
import { canUseDom } from 'helpers/misc/canUseDom';
import { MouseEventHandler, useEffect, useRef } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type PopupProps = {
    isVisible: boolean;
    onCloseCallback: () => void;
    hideCloseButton?: boolean;
};

const TEST_IDENTIFIER = 'layout-popup';

export const Popup: FC<PopupProps> = ({ isVisible, onCloseCallback, children, hideCloseButton, className }) => {
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

    if (!isVisible) {
        return null;
    }

    return (
        <Portal>
            <Overlay isActive={isVisible} onClick={onClickCloseActionHandler} />
            <div
                role="dialog"
                aria-modal
                data-testid={TEST_IDENTIFIER}
                className={twMergeCustom(
                    'fixed top-1/2 left-1/2 z-aboveOverlay flex max-h-full max-w-screen-lg -translate-x-1/2 -translate-y-1/2 cursor-auto flex-col rounded-xl bg-creamWhite p-1 shadow-2xl transition-opacity',
                    className,
                )}
            >
                {hideCloseButton !== true && (
                    <div className="flex h-9 items-center justify-end ">
                        <button
                            className="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border-0 bg-creamWhite text-xs text-grey no-underline outline-none"
                            onClick={onClickCloseActionHandler}
                        >
                            <Icon iconType="icon" icon="Remove" className="w-6 text-primary" />
                        </button>
                    </div>
                )}
                <div className="p-4">{children}</div>
            </div>
        </Portal>
    );
};
