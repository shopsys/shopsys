import { Image } from 'components/Basic/Image/Image';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import image404 from '/public/images/404.png';
import { ErrorPage, ErrorPageButtonLink, ErrorPageTextHeading, ErrorPageTextMain } from './ErrorPageElements';

export const Error404Content: FC = () => {
    const { t } = useTranslation();

    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.not_found);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    return (
        <CommonLayout title={t('Something wrong happened... Page not found')}>
            <Webline>
                <ErrorPage>
                    <div className="mb-8 max-w-sm">
                        <Image priority alt={t('404')} src={image404} />
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
