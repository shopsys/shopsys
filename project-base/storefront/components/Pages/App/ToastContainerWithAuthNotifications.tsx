import { useEffect, useState } from 'react';
import { AuthNotificationLoader } from './AuthNotificationLoader';
import { ToastContainerWrapper } from './ToastContainerWrapper';

export const ToastContainerWithAuthNotifications = () => {
    const [isToastContainerMounted, setIsToastContainerMounted] = useState(false);

    useEffect(() => {
        setIsToastContainerMounted(true);
    }, []);

    return (
        <>
            <ToastContainerWrapper />
            {isToastContainerMounted && <AuthNotificationLoader />}
        </>
    );
};
