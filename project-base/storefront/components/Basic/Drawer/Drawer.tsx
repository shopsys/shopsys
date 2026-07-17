import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { OVERLAY_PORTAL_ROOT_ID } from 'components/Basic/Portal/Portal';
import { AnimatePresence, m } from 'framer-motion';
import { AriaRole, ReactNode, useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { useFocusTrap } from 'utils/useFocusTrap';
import { useKeypress } from 'utils/useKeyPress';

const FOCUSABLE_ELEMENTS_SELECTOR =
    'button:not([tabindex="-1"]), a[href]:not([tabindex="-1"]), input:not([tabindex="-1"]), select:not([tabindex="-1"]), textarea:not([tabindex="-1"])';

type DrawerProps = {
    ariaLabel?: string;
    headerContent?: ReactNode;
    isActive: boolean;
    onClose?: () => void;
    role?: AriaRole;
    setIsActive: (value: boolean) => void;
    shouldRenderHeader?: boolean;
    title?: string;
};

export const Drawer: FC<DrawerProps> = ({
    ariaLabel,
    headerContent,
    isActive,
    onClose,
    role,
    setIsActive,
    shouldRenderHeader = true,
    title,
    children,
    className,
}) => {
    const isDesktop = useMediaMin('vl');
    const { t } = useTranslation();
    const drawerRef = useRef<HTMLDivElement>(null);
    const previousActiveElementRef = useRef<HTMLElement | null>(null);
    const [portalElement, setPortalElement] = useState<HTMLElement | null>(null);
    const closeDrawer = () => {
        setIsActive(false);
        onClose?.();
    };
    const drawerRole = role ?? 'dialog';

    useFocusTrap(isActive ? drawerRef : undefined);

    useEffect(() => {
        setPortalElement(document.getElementById(OVERLAY_PORTAL_ROOT_ID) ?? document.body);
    }, []);

    useKeypress('Escape', () => {
        if (isActive) {
            closeDrawer();
        }
    });

    useEffect(() => {
        if (!isActive || isDesktop) {
            return undefined;
        }

        previousActiveElementRef.current =
            document.activeElement instanceof HTMLElement ? document.activeElement : null;

        window.requestAnimationFrame(() => {
            const drawerElement = drawerRef.current;

            if (!drawerElement) {
                return;
            }

            const firstFocusableElement = drawerElement.querySelector<HTMLElement>(FOCUSABLE_ELEMENTS_SELECTOR);
            (firstFocusableElement ?? drawerElement).focus({ preventScroll: true });
        });

        return () => {
            previousActiveElementRef.current?.focus({ preventScroll: true });
            previousActiveElementRef.current = null;
        };
    }, [isActive, isDesktop]);

    useEffect(() => {
        if (!isActive || isDesktop) {
            return undefined;
        }

        const originalBodyOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';

        return () => {
            document.body.style.overflow = originalBodyOverflow;
        };
    }, [isActive, isDesktop]);

    if (isDesktop || !portalElement) {
        return null;
    }

    return createPortal(
        <>
            <Overlay isActive={isActive} onClick={closeDrawer} />

            <AnimatePresence initial={false}>
                {isActive && (
                    <m.div
                        animate={{ translateX: '0%' }}
                        aria-label={ariaLabel ?? title}
                        aria-modal={drawerRole === 'dialog' ? true : undefined}
                        exit={{ translateX: '100%' }}
                        initial={{ translateX: '100%' }}
                        ref={drawerRef}
                        role={drawerRole}
                        tabIndex={-1}
                        transition={{ duration: 0.2 }}
                        className={twMergeCustom(
                            'pointer-events-auto fixed top-0 right-0 z-maximum h-dvh min-w-90 overflow-y-auto rounded-none bg-background-default p-5',
                            className,
                        )}
                    >
                        {shouldRenderHeader &&
                            (headerContent ?? (
                                <div className="mb-5 flex flex-row items-center justify-between">
                                    <span className="w-full text-center font-secondary font-semibold">{title}</span>

                                    <button
                                        className="flex cursor-pointer items-center justify-center text-icon-less"
                                        tabIndex={0}
                                        title={t('Close')}
                                        type="button"
                                        onClick={closeDrawer}
                                    >
                                        <RemoveIcon className="size-6" />
                                    </button>
                                </div>
                            ))}

                        {children}
                    </m.div>
                )}
            </AnimatePresence>
        </>,
        portalElement,
    );
};
