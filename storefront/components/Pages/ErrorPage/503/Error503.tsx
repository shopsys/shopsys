import ErrorLayout from 'components/Layout/ErrorLayout';
import Webline from 'components/Layout/Webline';
import {
    ErrorPageStyled,
    ErrorPageTextHeadingStyled,
    ErrorPageTextMainStyled,
    ErrorPageTextStyled,
} from 'components/Pages/ErrorPage/ErrorPage.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import React, { FC } from 'react';

export const Error503: FC = () => {
    const t = useTypedTranslationFunction();

    return (
        <ErrorLayout>
            <Webline>
                <ErrorPageStyled>
                    <ErrorPageTextStyled>
                        <ErrorPageTextHeadingStyled>
                            {t('The page is currently under maintenance.')}
                        </ErrorPageTextHeadingStyled>
                        <ErrorPageTextMainStyled>{t('Please try again later or contact us.')}</ErrorPageTextMainStyled>
                    </ErrorPageTextStyled>
                </ErrorPageStyled>
            </Webline>
        </ErrorLayout>
    );
};
