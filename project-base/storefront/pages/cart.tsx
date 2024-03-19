import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { CartContent } from 'components/Pages/Cart/CartContent';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmCartViewEvent } from 'gtm/utils/pageViewEvents/useGtmCartViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const CartPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.cart);
    useGtmPageViewEvent(gtmStaticPageViewEvent);
    useGtmCartViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout title={t('Cart')}>
                <Webline>
                    <CartContent />
                </Webline>
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default CartPage;
