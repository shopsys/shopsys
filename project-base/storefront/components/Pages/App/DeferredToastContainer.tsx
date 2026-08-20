import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import dynamic from 'next/dynamic';
import { useState } from 'react';
import { hasAuthNotification } from 'utils/auth/authNotificationStorage';
import { useDeferredRender } from 'utils/useDeferredRender';

const ToastContainerWithAuthNotifications = dynamic(
    () =>
        import('components/Pages/App/ToastContainerWithAuthNotifications').then(
            (component) => component.ToastContainerWithAuthNotifications,
        ),
    { ssr: false },
);

export const DeferredToastContainer = () => {
    const { domainId } = useDomainConfig();
    const [hasInitialAuthNotification] = useState(() => hasAuthNotification(domainId));
    const shouldRender = useDeferredRender('tertiary_loaders');

    return shouldRender || hasInitialAuthNotification ? <ToastContainerWithAuthNotifications /> : null;
};
