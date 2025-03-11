'use client';

import { useAppConfig } from 'components/providers/AppConfigProvider';
import dynamic from 'next/dynamic';
import { useEffect, useState } from 'react';

const UserConsent = dynamic(
    () => import('app/_components/Blocks/UserConsent/UserConsent').then((component) => component.UserConsent),
    {
        ssr: true,
    },
);

const CONSENT_DELAY = 1250;

const useDeferredRender = (delay: number) => {
    const [shouldRender, setShouldRender] = useState(false);

    useEffect(() => {
        let timer: NodeJS.Timeout | undefined;

        if (!shouldRender) {
            const defer = delay;
            timer = setTimeout(() => {
                setShouldRender(true);
            }, defer);
        }

        return () => {
            clearTimeout(timer);
        };
    }, []);

    return shouldRender;
};

export const DeferredUserConsent = ({ isConsentUpdatePage }: { isConsentUpdatePage: boolean }) => {
    const { userConsentPolicyArticleUrl } = useAppConfig((settings) => settings.settings);
    const shouldRender = useDeferredRender(CONSENT_DELAY);

    return shouldRender && userConsentPolicyArticleUrl ? (
        <UserConsent isConsentUpdatePage={isConsentUpdatePage} />
    ) : null;
};
