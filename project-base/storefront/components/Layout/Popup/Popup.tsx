import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { TIDs } from 'cypress/tids';
import { AnimatePresence, m } from 'framer-motion';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useLayoutEffect, useRef, useState, useEffect } from 'react';
import { RemoveScroll } from 'react-remove-scroll';
import { useSessionStore } from 'store/useSessionStore';
import { twMergeCustom } from 'utils/twMerge';
import { useFocusTrap } from 'utils/useFocusTrap';
import { useKeypress } from 'utils/useKeyPress';
import useWindowDimensions from 'utils/useWindowDimensions';

const Overlay = dynamic(() => import('components/Basic/Overlay/Overlay').then((component) => component.Overlay));

type PopupProps = {
    title: string;
    ariaDescription?: string;
    hideCloseButton?: boolean;
    contentClassName?: string;
    key?: string;
    children?: React.ReactNode;
    className?: string;
    role?: 'dialog' | 'alertdialog';
};

export const Popup: React.FC<PopupProps> = ({
    title,
    ariaDescription,
    children,
    hideCloseButton,
    className,
    contentClassName,
    key,
    role = 'dialog',
}) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const windowDimensions = useWindowDimensions();
    const [popupPositions, setPopupPositions] = useState({ left: 0, top: 0 });
    const popupRef = useRef<HTMLDivElement>(null);
    const closeButtonRef = useRef<HTMLButtonElement>(null);
    const previousFocusRef = useRef<HTMLElement | null>(null);
    const ariaLabel = ariaDescription ? `${title}. ${ariaDescription}` : title;

    useKeypress('Escape', () => updatePortalContent(null));

    // Store the element that had focus before popup opened
    useEffect(() => {
        previousFocusRef.current = document.activeElement as HTMLElement;

        // Restore focus when popup unmounts
        return () => {
            if (previousFocusRef.current) {
                previousFocusRef.current.focus();
            }
        };
    }, []);

    // Focus on popup when it appears
    useEffect(() => {
        if (popupRef.current) {
            popupRef.current.focus();
        }
    }, [popupRef]);

    useLayoutEffect(() => {
        if (popupRef.current) {
            setPopupPositions({
                left: Math.round(windowDimensions.width / 2 - popupRef.current.offsetWidth / 2 - 20),
                top: Math.round(windowDimensions.height / 2 - popupRef.current.offsetHeight / 2),
            });
        }
    }, [windowDimensions, children]);

    useFocusTrap(popupRef);

    return (
        <div key={key}>
            <RemoveScroll>
                <Overlay isActive onClick={() => updatePortalContent(null)} />

                <AnimatePresence>
                    <m.div
                        key="popup"
                        animate={{ opacity: 1, scale: 1 }}
                        aria-label={ariaLabel}
                        aria-modal="true"
                        data-tid={TIDs.layout_popup}
                        exit={{ opacity: 0, scale: 0.8 }}
                        ref={popupRef}
                        role={role}
                        tabIndex={-1}
                        transition={{ duration: 0.2 }}
                        className={twMergeCustom(
                            'z-aboveOverlay bg-background-default fixed mx-5 flex max-h-[80vh] max-w-screen-lg cursor-auto flex-col rounded-md p-5 shadow-2xl',
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
                        <div className="mb-3 flex justify-between">
                            <span className="h3 outline-none" tabIndex={-1}>
                                {title}
                            </span>

                            {!hideCloseButton && (
                                <button
                                    aria-label={t('Close popup')}
                                    className="text-icon-less hover:text-icon-accent ml-auto flex size-9 cursor-pointer items-center justify-center rounded-sm"
                                    ref={closeButtonRef}
                                    tabIndex={0}
                                    onClick={() => updatePortalContent(null)}
                                >
                                    <RemoveIcon className="size-6" />
                                </button>
                            )}
                        </div>

                        <div className={twMergeCustom(contentClassName)}>{children}</div>
                    </m.div>
                </AnimatePresence>
            </RemoveScroll>
        </div>
    );
};
