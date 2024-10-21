import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { TIDs } from 'cypress/tids';
import { AnimatePresence, m } from 'framer-motion';
import dynamic from 'next/dynamic';
import { useLayoutEffect, useRef, useState } from 'react';
import { RemoveScroll } from 'react-remove-scroll';
import { useSessionStore } from 'store/useSessionStore';
import { twMergeCustom } from 'utils/twMerge';
import { useKeypress } from 'utils/useKeyPress';
import useWindowDimensions from 'utils/useWindowDimensions';

const Overlay = dynamic(() => import('components/Basic/Overlay/Overlay').then((component) => component.Overlay));

type PopupProps = {
    hideCloseButton?: boolean;
    contentClassName?: string;
    key?: string;
};

export const Popup: FC<PopupProps> = ({ children, hideCloseButton, className, contentClassName, key }) => {
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const windowDimensions = useWindowDimensions();
    const [popupPositions, setPopupPositions] = useState({ left: 0, top: 0 });
    const popupRef = useRef<HTMLDivElement>(null);

    useKeypress('Escape', () => updatePortalContent(null));

    useLayoutEffect(() => {
        if (popupRef.current) {
            setPopupPositions({
                left: Math.round(windowDimensions.width / 2 - popupRef.current.offsetWidth / 2),
                top: Math.round(windowDimensions.height / 2 - popupRef.current.offsetHeight / 2),
            });
        }
    }, [windowDimensions]);

    return (
        <div key={key}>
            <RemoveScroll>
                <Overlay isActive onClick={() => updatePortalContent(null)} />
                <AnimatePresence>
                    <m.div
                        key="popup"
                        aria-modal
                        animate={{ opacity: 1, scale: 1 }}
                        exit={{ opacity: 0, scale: 0.8 }}
                        ref={popupRef}
                        role="dialog"
                        tid={TIDs.layout_popup}
                        transition={{ duration: 0.2 }}
                        className={twMergeCustom(
                            'fixed z-aboveOverlay flex max-h-[80vh] max-w-screen-lg cursor-auto flex-col rounded bg-background p-1 shadow-2xl',
                            className,
                        )}
                        initial={{
                            opacity: 0,
                            scale: 0.8,
                        }}
                        style={{
                            left: popupPositions.left,
                            top: popupPositions.top,
                        }}
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
                                    className="flex size-9 cursor-pointer items-center justify-center rounded-full border-0 text-xs text-textAccent no-underline outline-none"
                                    onClick={() => updatePortalContent(null)}
                                >
                                    <RemoveIcon className="w-6" />
                                </button>
                            </div>
                        )}
                        <div className={twMergeCustom('p-4', contentClassName)}>{children}</div>
                    </m.div>
                </AnimatePresence>
            </RemoveScroll>
        </div>
    );
};
