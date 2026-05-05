import { CommonLayout } from 'components/Layout/CommonLayout';
import { ContactContent } from 'components/Pages/Contact/ContactContent';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const ContactFormPage: FC<ServerSidePropsType> = () => {
    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.contact);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    return (
        <CommonLayout>
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
