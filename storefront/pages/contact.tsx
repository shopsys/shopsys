import { CommonLayout } from 'components/Layout/CommonLayout';
import { ContactContent } from 'components/Pages/Contact/ContactContent';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import React, { FC } from 'react';
import { nextReduxWrapper } from 'redux/main';

const ContactPage: FC<ServerSidePropsType> = () => {
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('contact');
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <CommonLayout>
            <ContactContent />
        </CommonLayout>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient(
        (redisClient) => async (context) => initServerSideProps({ context, store, redisClient }),
        store,
    ),
);

export default ContactPage;
