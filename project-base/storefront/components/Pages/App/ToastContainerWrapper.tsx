import { CloseIcon } from 'components/Basic/Icon/CloseIcon';
import { IconButton } from 'components/Forms/Button/IconButton';
import { TIDs } from 'cypress/tids';
import { type RefObject, useEffect, useRef } from 'react';
import { type CloseButtonProps, Slide, ToastContainer, type ToastItem, toast } from 'react-toastify';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import 'react-toastify/dist/ReactToastify.css';

/**
 * A message does not time out while it has the focus (see the :focus-within rule in base.css),
 * so it disappears only when it is closed or dismissed. The focus then returns to where the user was.
 */
const bindNotificationFocusEvents = (
    notifications: HTMLDivElement | null,
    focusBeforeNotificationsRef: RefObject<HTMLElement | null>,
) => {
    if (!notifications) {
        return undefined;
    }

    const isOutsideNotifications = (element: EventTarget | null) =>
        !(element instanceof Node) || !notifications.contains(element);

    const rememberFocusBeforeNotifications = (event: FocusEvent) => {
        if (isOutsideNotifications(event.relatedTarget)) {
            focusBeforeNotificationsRef.current =
                event.relatedTarget instanceof HTMLElement ? event.relatedTarget : null;
        }
    };

    // Alt+T pauses every message, so they continue once the focus leaves the notifications
    const resumeWhenFocusLeaves = (event: FocusEvent) => {
        if (isOutsideNotifications(event.relatedTarget)) {
            focusBeforeNotificationsRef.current = null;
            toast.play();
        }
    };

    const returnFocusFromRemovedMessage = ({ id, status }: ToastItem) => {
        const removedMessage = status === 'removed' ? document.getElementById(String(id)) : null;

        if (!removedMessage?.contains(document.activeElement)) {
            return;
        }

        const focusBeforeNotifications = focusBeforeNotificationsRef.current;

        if (focusBeforeNotifications?.isConnected) {
            focusBeforeNotifications.focus();
        } else {
            toast.play();
        }
    };

    notifications.addEventListener('focusin', rememberFocusBeforeNotifications);
    notifications.addEventListener('focusout', resumeWhenFocusLeaves);
    const unsubscribeFromMessageChanges = toast.onChange(returnFocusFromRemovedMessage);

    return () => {
        notifications.removeEventListener('focusin', rememberFocusBeforeNotifications);
        notifications.removeEventListener('focusout', resumeWhenFocusLeaves);
        unsubscribeFromMessageChanges();
    };
};

export const ToastContainerWrapper = () => {
    const { t } = useTranslation();
    const notificationsRef = useRef<HTMLDivElement>(null);
    const focusBeforeNotificationsRef = useRef<HTMLElement | null>(null);
    // Alt+T is react-toastify's built-in shortcut that focuses the notifications and pauses their timers
    const notificationsLabel = `${t('Notifications', { ns: 'accessibility' })} (Alt+T)`;

    useEffect(() => bindNotificationFocusEvents(notificationsRef.current, focusBeforeNotificationsRef), []);

    const closeButton = ({ closeToast }: CloseButtonProps) => (
        <IconButton
            className="ml-auto"
            Icon={CloseIcon}
            shape="rounded"
            size="small"
            tid={TIDs.toast_close_button}
            title={t('Close')}
            variant="ghost"
            onClick={() => closeToast(true)}
        />
    );

    return (
        <div ref={notificationsRef}>
            {/* biome-ignore lint/a11y/useValidAriaRole: react-toastify passes this role to every message. The container is already a polite live region, so a role of its own could make screen readers announce each message twice. */}
            <ToastContainer
                aria-label={notificationsLabel}
                autoClose={6000}
                closeButton={closeButton}
                hideProgressBar
                position="top-center"
                role={undefined}
                theme="colored"
                transition={Slide}
            />
        </div>
    );
};
