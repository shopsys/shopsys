import { ErrorPage, ErrorPageTextHeading, ErrorPageTextMain } from './ErrorPageElements';
import { ErrorLayout } from 'components/Layout/ErrorLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import React, { useEffect } from 'react';

export const Error503Content: FC = () => {
    const { t } = useTranslation();
    const router = useRouter();

    useEffect(() => {
        const onRouteChangeComplete = () => router.reload();

        router.events.on('routeChangeComplete', onRouteChangeComplete);

        return () => {
            router.events.off('routeChangeComplete', onRouteChangeComplete);
        };
    }, []);

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
