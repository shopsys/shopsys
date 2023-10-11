import { ErrorPage, ErrorPageButtonLink, ErrorPageTextHeading, ErrorPageTextMain } from './ErrorPageElements';
import { ErrorLayout } from 'components/Layout/ErrorLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import React, { useEffect } from 'react';
import { FallbackProps } from 'react-error-boundary';

export const Error500ContentWithBoundary: FC<FallbackProps> = ({ resetErrorBoundary }) => {
    const router = useRouter();

    useEffect(() => {
        router.events.on('routeChangeComplete', resetErrorBoundary);

        return () => {
            router.events.off('routeChangeComplete', resetErrorBoundary);
        };
    }, [resetErrorBoundary, router.events]);

    return <Error500Content />;
};

export const Error500Content: FC = () => {
    const { t } = useTranslation();

    return (
        <ErrorLayout>
            <Webline>
                <ErrorPage isWithoutImage>
                    <ErrorPageTextHeading>{t('Something went wrong.')}</ErrorPageTextHeading>
                    <ErrorPageTextMain>{t('Please try again later or contact us.')}</ErrorPageTextMain>

                    <ErrorPageButtonLink href="/">{t('Back to shop')}</ErrorPageButtonLink>
                </ErrorPage>
            </Webline>
        </ErrorLayout>
    );
};
