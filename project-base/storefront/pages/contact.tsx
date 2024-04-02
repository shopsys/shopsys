import { CommonLayout } from 'components/Layout/CommonLayout';
import { ContactContent } from 'components/Pages/Contact/ContactContent';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import React from 'react';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const ContactPage: FC<ServerSidePropsType> = () => {
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.contact);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <CommonLayout>
            <ContactContent />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default ContactPage;
