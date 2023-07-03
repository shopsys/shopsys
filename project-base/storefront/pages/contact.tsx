import { CommonLayout } from 'components/Layout/CommonLayout';
import { ContactContent } from 'components/Pages/Contact/ContactContent';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import React from 'react';
import { GtmPageType } from 'types/gtm/enums';

const ContactPage: FC<ServerSidePropsType> = () => {
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.contact);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <CommonLayout>
            <ContactContent />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWithRedisClient(
    (redisClient) => async (context) => initServerSideProps({ context, redisClient }),
);

export default ContactPage;
