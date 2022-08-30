import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import {
    ErrorPageButtonLinkStyled,
    ErrorPageImageStyled,
    ErrorPageStyled,
    ErrorPageTextHeadingStyled,
    ErrorPageTextMainStyled,
    ErrorPageTextStyled,
} from 'components/Pages/ErrorPage/ErrorPage.style';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import NextLink from 'next/link';
import React, { FC } from 'react';

export const Error404Content: FC = () => {
    const t = useTypedTranslationFunction();

    return (
        <CommonLayout>
            <Webline>
                <ErrorPageStyled>
                    <ErrorPageTextStyled>
                        <ErrorPageTextHeadingStyled>
                            {t('We have nothing to show you at this url.')}
                        </ErrorPageTextHeadingStyled>
                        <ErrorPageTextMainStyled>
                            {t('But at other addresses we have a lot for you...')}
                        </ErrorPageTextMainStyled>

                        <NextLink href="/" passHref>
                            <ErrorPageButtonLinkStyled>{t('Back to shop')}</ErrorPageButtonLinkStyled>
                        </NextLink>
                    </ErrorPageTextStyled>
                    <ErrorPageImageStyled>
                        <picture>
                            <source media="(max-width: 768px)" srcSet="/public/frontend/images/404_m.png" />
                            <source srcSet="/public/frontend/images/404_desktop_2x.jpg 2x, /public/frontend/images/404_desktop.jpg 1x" />
                            <img loading="lazy" data-src="/public/frontend/images/404_desktop.jpg 1x" alt={t('404')} />
                        </picture>
                    </ErrorPageImageStyled>
                </ErrorPageStyled>
            </Webline>
        </CommonLayout>
    );
};
