import { RemoveIcon } from 'components/Basic/Icon/IconsSvg';
import { Portal } from 'components/Basic/Portal/Portal';
import { twMergeCustom } from 'helpers/twMerge';
import dynamic from 'next/dynamic';
import { MouseEventHandler, useEffect, useRef } from 'react';

const Overlay = dynamic(() => import('components/Basic/Overlay/Overlay').then((component) => component.Overlay));

type PopupProps = {
    onCloseCallback: () => void;
    hideCloseButton?: boolean;
};

const TEST_IDENTIFIER = 'layout-popup';

export const Popup: FC<PopupProps> = ({ onCloseCallback, children, hideCloseButton, className }) => {
    const onEscapeButtonPressHandler = useRef((event: KeyboardEvent): void => {
        if (event.key === 'Escape') {
            onCloseCallback();
        }
    }).current;

    useEffect(() => {
        document.addEventListener('keydown', onEscapeButtonPressHandler);

        return () => document.removeEventListener('keydown', onEscapeButtonPressHandler);
    }, []);

    const onClickCloseActionHandler: MouseEventHandler<HTMLElement> = () => {
        onCloseCallback();
    };

    return (
        <Portal>
            <Overlay isActive onClick={onClickCloseActionHandler} />
            <div
                aria-modal
                data-testid={TEST_IDENTIFIER}
                role="dialog"
                className={twMergeCustom(
                    'fixed top-1/2 left-1/2 z-aboveOverlay flex max-h-full max-w-screen-lg -translate-x-1/2 -translate-y-1/2 cursor-auto flex-col rounded bg-creamWhite p-1 shadow-2xl transition-opacity',
                    className,
                )}
            >
                {!hideCloseButton && (
                    <div className="flex h-9 items-center justify-end ">
                        <button
                            className="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border-0 bg-creamWhite text-xs text-grey no-underline outline-none"
                            onClick={onClickCloseActionHandler}
                        >
                            <RemoveIcon className="w-6 text-primary" />
                        </button>
                    </div>
                )}
                <div className="p-4">{children}</div>
            </div>
        </Portal>
    );
};
