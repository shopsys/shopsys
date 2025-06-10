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
    hideCloseButton?: boolean;
    contentClassName?: string;
    key?: string;
    ariaLabelledBy?: string;
    ariaDescribedBy?: string;
};

export const Popup: FC<PopupProps> = ({
    children,
    hideCloseButton,
    className,
    contentClassName,
    key,
    ariaLabelledBy,
    ariaDescribedBy,
}) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const windowDimensions = useWindowDimensions();
    const [popupPositions, setPopupPositions] = useState({ left: 0, top: 0 });
    const popupRef = useRef<HTMLDivElement>(null);
    const closeButtonRef = useRef<HTMLButtonElement>(null);
    const previousFocusRef = useRef<HTMLElement | null>(null);

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

    useKeypress('Escape', () => updatePortalContent(null));

    useLayoutEffect(() => {
        if (popupRef.current) {
            setPopupPositions({
                left: Math.round(windowDimensions.width / 2 - popupRef.current.offsetWidth / 2),
                top: Math.round(windowDimensions.height / 2 - popupRef.current.offsetHeight / 2),
            });
        }
    }, [windowDimensions, children]);

    // Focus management when popup appears
    useEffect(() => {
        if (popupRef.current) {
            let focusTarget: HTMLElement;

            if (!hideCloseButton && closeButtonRef.current) {
                // Focus close button if available
                focusTarget = closeButtonRef.current;
            } else {
                // Find first focusable element in content
                const focusableElements = popupRef.current.querySelectorAll(
                    'button, a[href], input, select, textarea, [tabindex]:not([tabindex="-1"])',
                );
                focusTarget = focusableElements.length > 0 ? (focusableElements[0] as HTMLElement) : popupRef.current;
            }

            focusTarget.focus();
        }
    }, [hideCloseButton]);

    useFocusTrap(popupRef);

    return (
        <div key={key}>
            <RemoveScroll>
                <Overlay isActive onClick={() => updatePortalContent(null)} />
                <AnimatePresence>
                    <m.div
                        key="popup"
                        animate={{ opacity: 1, scale: 1 }}
                        aria-describedby={ariaDescribedBy}
                        aria-labelledby={ariaLabelledBy}
                        aria-modal="true"
                        data-tid={TIDs.layout_popup}
                        exit={{ opacity: 0, scale: 0.8 }}
                        ref={popupRef}
                        role="dialog"
                        tabIndex={-1}
                        transition={{ duration: 0.2 }}
                        className={twMergeCustom(
                            'z-aboveOverlay bg-background-default fixed flex max-h-[80vh] max-w-screen-lg cursor-auto flex-col rounded-sm p-1 shadow-2xl',
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
                            <button
                                className="text-icon-less hover:text-icon-accent ml-auto flex size-9 cursor-pointer items-center justify-center rounded-sm"
                                ref={closeButtonRef}
                                tabIndex={0}
                                title={t('Close dialog')}
                                onClick={() => updatePortalContent(null)}
                            >
                                <RemoveIcon className="size-6" />
                            </button>
                        )}
                        <div className={twMergeCustom('p-4', contentClassName)}>{children}</div>
                    </m.div>
                </AnimatePresence>
            </RemoveScroll>
        </div>
    );
};
