import { CommonLayout } from 'components/Layout/CommonLayout';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ErrorPage } from './ErrorPage';
import { ErrorPageUsefulLinks } from './ErrorPageUsefulLinks';

export const Error404Content: FC = () => {
    const { t } = useTranslation();

    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.not_found);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    return (
        <CommonLayout title={t('Something wrong happened... Page not found')}>
            <ErrorPage
                heading={t('This page got lost.')}
                statusCode={t('404')}
                text={t('The address may be wrong, but there is still plenty to discover.')}
            >
                <ErrorPageUsefulLinks />
            </ErrorPage>
        </CommonLayout>
    );
};
