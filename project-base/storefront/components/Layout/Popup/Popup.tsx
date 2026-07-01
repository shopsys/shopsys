import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { IconButton } from 'components/Forms/Button/IconButton';
import { TIDs } from 'cypress/tids';
import { AnimatePresence, m } from 'framer-motion';
import dynamic from 'next/dynamic';
import { useEffect, useEffectEvent, useId, useLayoutEffect, useRef, useState } from 'react';
import { RemoveScroll } from 'react-remove-scroll';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { useFocusTrap } from 'utils/useFocusTrap';
import { useKeypress } from 'utils/useKeyPress';
import useWindowDimensions from 'utils/useWindowDimensions';

const Overlay = dynamic(() => import('components/Basic/Overlay/Overlay').then((component) => component.Overlay));

type PopupProps = {
    title: string;
    ariaDescription?: string;
    hideCloseButton?: boolean;
    isTitleHidden?: boolean;
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
    isTitleHidden,
    className,
    contentClassName,
    key,
    role = 'dialog',
}) => {
    const { t } = useTranslation();
    const closePortalContent = useSessionStore((s) => s.closePortalContent);
    const windowDimensions = useWindowDimensions();
    const [popupPositions, setPopupPositions] = useState({ left: 0, top: 0 });
    const popupRef = useRef<HTMLDivElement>(null);
    const titleRef = useRef<HTMLHeadingElement>(null);
    const closeButtonRef = useRef<HTMLButtonElement>(null);
    const popupId = useId();
    const titleId = `${popupId}-title`;
    const descriptionId = ariaDescription ? `${popupId}-description` : undefined;
    const storeCurrentFocus = useSessionStore((s) => s.storeCurrentFocus);

    const handleClosePopup = () => {
        closePortalContent();
    };

    const onStoreAndFocus = useEffectEvent(() => {
        storeCurrentFocus();

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                (titleRef.current ?? popupRef.current)?.focus({ preventScroll: true });
            });
        });
    });

    // Focus on popup when it appears
    useEffect(() => {
        onStoreAndFocus();
    }, []);

    useLayoutEffect(() => {
        if (!popupRef.current) {
            return undefined;
        }

        const updatePosition = () => {
            if (popupRef.current) {
                setPopupPositions({
                    left: Math.round(windowDimensions.width / 2 - popupRef.current.offsetWidth / 2 - 20),
                    top: Math.round(windowDimensions.height / 2 - popupRef.current.offsetHeight / 2),
                });
            }
        };

        updatePosition();

        const observer = new ResizeObserver(updatePosition);
        observer.observe(popupRef.current);

        return () => {
            observer.disconnect();
        };
    }, [windowDimensions]);

    useKeypress('Escape', () => handleClosePopup());

    useFocusTrap(popupRef);

    return (
        <div key={key}>
            <RemoveScroll>
                <Overlay isActive onClick={handleClosePopup} />

                <AnimatePresence>
                    <m.div
                        key="popup"
                        animate={{ opacity: 1, scale: 1 }}
                        aria-describedby={descriptionId}
                        aria-labelledby={titleId}
                        aria-modal="true"
                        data-tid={TIDs.layout_popup}
                        exit={{ opacity: 0, scale: 0.8 }}
                        ref={popupRef}
                        role={role}
                        tabIndex={-1}
                        transition={{ duration: 0.2 }}
                        className={twMergeCustom(
                            'fixed z-aboveOverlay mx-5 flex max-h-[80vh] max-w-screen-lg cursor-auto flex-col rounded-md bg-background-default p-5 shadow-2xl',
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
                            <h2
                                aria-describedby={descriptionId}
                                className={twMergeCustom('h3 outline-hidden', isTitleHidden && 'sr-only')}
                                id={titleId}
                                ref={titleRef}
                                tabIndex={-1}
                            >
                                {title}
                            </h2>

                            {!hideCloseButton && (
                                <IconButton
                                    Icon={RemoveIcon}
                                    ariaLabel={t('Close popup', { ns: 'accessibility' })}
                                    buttonRef={closeButtonRef}
                                    className="ml-auto"
                                    title={t('Close popup')}
                                    onClick={handleClosePopup}
                                />
                            )}
                        </div>

                        {ariaDescription && (
                            <p className="sr-only" id={descriptionId}>
                                {ariaDescription}
                            </p>
                        )}

                        <div className={twMergeCustom(contentClassName)}>{children}</div>
                    </m.div>
                </AnimatePresence>
            </RemoveScroll>
        </div>
    );
};
