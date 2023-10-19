import { ErrorPage, ErrorPageButtonLink, ErrorPageTextHeading, ErrorPageTextMain } from './ErrorPageElements';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { GtmPageType } from 'gtm/types/enums';
import useTranslation from 'next-translate/useTranslation';
import React from 'react';

export const Error404Content: FC = () => {
    const { t } = useTranslation();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.not_found);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <CommonLayout title={t('Something wrong happened... Page not found')}>
            <Webline>
                <ErrorPage>
                    <div className="mb-8 max-w-sm">
                        <img alt={t('404')} loading="lazy" src="/public/frontend/images/404_m.png" />
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
