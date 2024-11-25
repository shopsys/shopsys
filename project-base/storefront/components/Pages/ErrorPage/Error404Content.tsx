import { Error404Headless } from './Error404Headless';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';

export const Error404Content: FC = () => {
    const { t } = useTranslation();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.not_found);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <CommonLayout title={t('Something wrong happened... Page not found')}>
            <Webline>
                <Error404Headless
                    backButtonHref="/"
                    backButtonText={t('Back to shop')}
                    headingText={t('We have nothing to show you at this url.')}
                    imageAlt={t('404')}
                    mainText={t('But at other addresses we have a lot for you...')}
                />
            </Webline>
        </CommonLayout>
    );
};
