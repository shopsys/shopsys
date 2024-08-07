import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { TIDs } from 'cypress/tids';
import dynamic from 'next/dynamic';
import { RemoveScroll } from 'react-remove-scroll';
import { useSessionStore } from 'store/useSessionStore';
import { twMergeCustom } from 'utils/twMerge';
import { useKeypress } from 'utils/useKeyPress';

const Overlay = dynamic(() => import('components/Basic/Overlay/Overlay').then((component) => component.Overlay));

type PopupProps = {
    hideCloseButton?: boolean;
    contentClassName?: string;
    key?: string;
};

export const Popup: FC<PopupProps> = ({ children, hideCloseButton, className, contentClassName, key }) => {
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    useKeypress('Escape', () => updatePortalContent(null));

    return (
        <div key={key}>
            <RemoveScroll>
                <Overlay isActive onClick={() => updatePortalContent(null)} />
                <div
                    aria-modal
                    role="dialog"
                    tid={TIDs.layout_popup}
                    className={twMergeCustom(
                        'fixed top-1/2 left-1/2 z-aboveOverlay flex max-h-full max-w-screen-lg -translate-x-1/2 -translate-y-1/2 cursor-auto flex-col rounded bg-background p-1 shadow-2xl transition-opacity',
                        className,
                    )}
                    onMouseDown={(event) => {
                        event.stopPropagation();
                    }}
                    onTouchMove={(event) => {
                        event.stopPropagation();
                    }}
                >
                    {!hideCloseButton && (
                        <div className="flex h-9 items-center justify-end ">
                            <button
                                className="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border-0 text-xs text-textAccent no-underline outline-none"
                                onClick={() => updatePortalContent(null)}
                            >
                                <RemoveIcon className="w-6" />
                            </button>
                        </div>
                    )}
                    <div className={twMergeCustom('p-4', contentClassName)}>{children}</div>
                </div>
            </RemoveScroll>
        </div>
    );
};
