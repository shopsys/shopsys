import {
    ErrorPageImageStyled,
    ErrorPageStyled,
    ErrorPageTextButtonStyled,
    ErrorPageTextHeadingStyled,
    ErrorPageTextMainStyled,
    ErrorPageTextStyled,
} from './Error404.style';
import CommonLayout from 'components/Layout/CommonLayout';
import { NextPage } from 'next';
import React from 'react';
import { useRouter } from 'next/router';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const Error404: NextPage = () => {
    const t = useTypedTranslationFunction();
    const router = useRouter();
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

                        <ErrorPageTextButtonStyled
                            type="button"
                            onClick={() =>
                                router.push({
                                    pathname: '/',
                                })
                            }
                        >
                            {t('Back to shop')}
                        </ErrorPageTextButtonStyled>
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
