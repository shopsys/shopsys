import { ErrorPage, ErrorPageButtonLink, ErrorPageTextHeading, ErrorPageTextMain } from './ErrorPageElements';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import React, { FC } from 'react';

export const Error404Content: FC = () => {
    const t = useTypedTranslationFunction();

    return (
        <CommonLayout title={t('Something wrong happened... Page not found')}>
            <Webline>
                <ErrorPage>
                    <div className="mb-8 max-w-sm">
                        <img loading="lazy" src="/public/frontend/images/404_m.png" alt={t('404')} />
                    </div>
                    <div>
                        <ErrorPageTextHeading>{t('We have nothing to show you at this url.')}</ErrorPageTextHeading>
                        <ErrorPageTextMain>{t('But at other addresses we have a lot for you...')}</ErrorPageTextMain>

                        <ErrorPageButtonLink href="/">{t('Back to shop')}</ErrorPageButtonLink>
                    </div>
                </ErrorPage>
            </Webline>
        </CommonLayout>
    );
};
