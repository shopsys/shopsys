import { CommonLayout } from 'components/Layout/CommonLayout';
import { ContactContent } from 'components/Pages/Contact/ContactContent';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const ContactFormPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.contact);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    return (
        <CommonLayout title={t('Contact form')}>
            <ContactContent />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({
                context,
                currentCustomerUserPrefetchMode: 'full',
                redisClient,
                domainConfig,
                t,
            }),
);

export default ContactFormPage;
