import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useRouter } from 'next/router';
import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type AbandonedCartPageProps = ServerSidePropsType & { cartUuid?: string };

const AbandonedCartPage: FC<AbandonedCartPageProps> = ({ cartUuid }) => {
    const router = useRouter();
    const { url } = useDomainConfig();
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);

    useEffect(() => {
        if (typeof cartUuid === 'string') {
            updateCartUuid(cartUuid);
        }
        router.replace(getInternationalizedStaticUrls(['/cart'], url)[0]);
    }, [cartUuid, router, updateCartUuid, url]);

    return null;
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({
                context,
                redisClient,
                domainConfig,
                t,
                additionalProps: { cartUuid: context.params?.cartUuid },
            }),
);

export default AbandonedCartPage;
