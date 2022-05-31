import Error404 from 'components/Pages/ErrorPage/404';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { FC } from 'react';
import { useGtmStaticPageViewEvent } from 'utils/Gtm/EventFactories';

const Page404: FC = () => {
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('404');
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return <Error404 />;
};

export default Page404;
