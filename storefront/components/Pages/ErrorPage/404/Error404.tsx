import CommonLayout from 'components/Layout/CommonLayout';
import Webline from 'components/Layout/Webline';
import {
    ErrorPageButtonLinkStyled,
    ErrorPageImageStyled,
    ErrorPageStyled,
    ErrorPageTextHeadingStyled,
    ErrorPageTextMainStyled,
    ErrorPageTextStyled,
} from 'components/Pages/ErrorPage/ErrorPage.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { NextPage } from 'next';
import Link from 'next/link';
import React from 'react';

const Error404: NextPage = () => {
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

                        <Link href="/" passHref>
                            <ErrorPageButtonLinkStyled>{t('Back to shop')}</ErrorPageButtonLinkStyled>
                        </Link>
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

export default Error404;
