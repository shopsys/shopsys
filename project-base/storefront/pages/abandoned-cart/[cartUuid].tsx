import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getServerSidePropsWrapper } from 'helpers/misc/getServerSidePropsWrapper';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useRouter } from 'next/router';
import { useEffect } from 'react';
import { usePersistStore } from 'store/zustand/usePersistStore';

type AbandonedCartPageProps = { cartUuid?: string };

const AbandonedCartPage: FC<AbandonedCartPageProps> = ({ cartUuid }) => {
    const router = useRouter();
    const { url } = useDomainConfig();
    const updateUserState = usePersistStore((store) => store.updateUserState);

    useEffect(() => {
        if (typeof cartUuid === 'string') {
            updateUserState({ cartUuid });
        }
        router.replace(getInternationalizedStaticUrls(['/cart'], url)[0] ?? '/');
    }, [cartUuid, router, updateUserState, url]);

    return null;
};

export const getServerSideProps = getServerSidePropsWrapper(() => async (context) => ({
    props: { cartUuid: context.params?.cartUuid },
}));

export default AbandonedCartPage;
