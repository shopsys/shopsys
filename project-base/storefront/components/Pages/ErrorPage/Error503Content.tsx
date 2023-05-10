import { ErrorPage, ErrorPageTextHeading, ErrorPageTextMain } from './ErrorPageElements';
import { ErrorLayout } from 'components/Layout/ErrorLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import React from 'react';

export const Error503Content: FC = () => {
    const t = useTypedTranslationFunction();

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
