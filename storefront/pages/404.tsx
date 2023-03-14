import { Error404Content } from 'components/Pages/ErrorPage/Error404Content';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';

const Page404: FC = () => {
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('404');
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return <Error404Content />;
};

export default Page404;
