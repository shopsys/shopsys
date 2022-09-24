import { ErrorLayout } from 'components/Layout/ErrorLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import {
    ErrorPageButtonLinkStyled,
    ErrorPageStyled,
    ErrorPageTextHeadingStyled,
    ErrorPageTextMainStyled,
    ErrorPageTextStyled,
} from 'components/Pages/ErrorPage/ErrorPage.style';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import NextLink from 'next/link';
import { useRouter } from 'next/router';
import React, { FC, useEffect } from 'react';
import { FallbackProps } from 'react-error-boundary';

export const Error500ContentWithBoundary: FC<FallbackProps> = ({ resetErrorBoundary }) => {
    const router = useRouter();

    useEffect(() => {
        const handleResetFromErrorState = () => resetErrorBoundary();

        router.events.on('routeChangeComplete', handleResetFromErrorState);

        return () => {
            router.events.off('routeChangeComplete', handleResetFromErrorState);
        };
    }, [resetErrorBoundary, router.events]);

    return <Error500Content />;
};

export const Error500Content: FC = () => {
    const t = useTypedTranslationFunction();

    return (
        <ErrorLayout>
            <Webline>
                <ErrorPageStyled>
                    <ErrorPageTextStyled>
                        <ErrorPageTextHeadingStyled>{t('Something went wrong.')}</ErrorPageTextHeadingStyled>
                        <ErrorPageTextMainStyled>{t('Please try again later or contact us.')}</ErrorPageTextMainStyled>

                        <NextLink href="/" passHref>
                            <ErrorPageButtonLinkStyled>{t('Back to shop')}</ErrorPageButtonLinkStyled>
                        </NextLink>
                    </ErrorPageTextStyled>
                </ErrorPageStyled>
            </Webline>
        </ErrorLayout>
    );
};
