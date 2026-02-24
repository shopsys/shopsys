import { ErrorPage, ErrorPageTextHeading, ErrorPageTextMain } from './ErrorPageElements';
import { ErrorLayout } from 'components/Layout/ErrorLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { useRouter } from 'next/router';
import React, { useEffect, useEffectEvent } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const Error503Content: FC = () => {
    const { t } = useTranslation();
    const router = useRouter();

    const onRouteChangeComplete = useEffectEvent(() => {
        router.reload();
    });

    useEffect(() => {
        router.events.on('routeChangeComplete', onRouteChangeComplete);

        return () => {
            router.events.off('routeChangeComplete', onRouteChangeComplete);
        };
    }, [router.events]);

    return (
        <ErrorLayout>
            <Webline>
                <ErrorPage>
                    <ErrorPageTextHeading>{t('The page is currently under maintenance.')}</ErrorPageTextHeading>
                    <ErrorPageTextMain>{t('Please try again later or contact us.')}</ErrorPageTextMain>
                </ErrorPage>
            </Webline>
        </ErrorLayout>
    );
};
