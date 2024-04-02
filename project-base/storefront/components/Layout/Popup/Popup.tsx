import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { Portal } from 'components/Basic/Portal/Portal';
import { TIDs } from 'cypress/tids';
import dynamic from 'next/dynamic';
import { twMergeCustom } from 'utils/twMerge';
import { useKeypress } from 'utils/useKeyPress';

const Overlay = dynamic(() => import('components/Basic/Overlay/Overlay').then((component) => component.Overlay));

type PopupProps = {
    onCloseCallback: () => void;
    hideCloseButton?: boolean;
    contentClassName?: string;
};

export const Popup: FC<PopupProps> = ({ onCloseCallback, children, hideCloseButton, className, contentClassName }) => {
    useKeypress('Escape', onCloseCallback);

    return (
        <Portal>
            <Overlay isActive onClick={onCloseCallback} />
            <div
                aria-modal
                role="dialog"
                tid={TIDs.layout_popup}
                className={twMergeCustom(
                    'fixed top-1/2 left-1/2 z-aboveOverlay flex max-h-full max-w-screen-lg -translate-x-1/2 -translate-y-1/2 cursor-auto flex-col rounded bg-creamWhite p-1 shadow-2xl transition-opacity',
                    className,
                )}
            >
                {!hideCloseButton && (
                    <div className="flex h-9 items-center justify-end ">
                        <button
                            className="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border-0 bg-creamWhite text-xs text-grey no-underline outline-none"
                            onClick={onCloseCallback}
                        >
                            <RemoveIcon className="w-6 text-primary" />
                        </button>
                    </div>
                )}
                <div className={twMergeCustom('p-4', contentClassName)}>{children}</div>
            </div>
        </Portal>
    );
};
