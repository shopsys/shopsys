import CommonLayout from 'components/Layout/CommonLayout';
import Contact from 'components/Pages/Contact';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import React, { FC } from 'react';
import { nextReduxWrapper } from 'redux/main';
import { useGtmStaticPageViewEvent } from 'utils/Gtm/EventFactories';

const ContactPage: FC<ServerSidePropsType> = () => {
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('contact');
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <CommonLayout>
            <Contact />
        </CommonLayout>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store);
});

export default ContactPage;
