import ErrorLayout from 'components/Layout/ErrorLayout';
import Webline from 'components/Layout/Webline';
import {
    ErrorPageButtonLinkStyled,
    ErrorPageStyled,
    ErrorPageTextHeadingStyled,
    ErrorPageTextMainStyled,
    ErrorPageTextStyled,
} from 'components/Pages/ErrorPage/ErrorPage.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import NextLink from 'next/link';
import React, { FC } from 'react';

const Error500: FC = () => {
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

export default Error500;
