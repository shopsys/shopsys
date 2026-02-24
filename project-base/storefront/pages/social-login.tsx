import { useRouter } from 'next/router';
import { useEffect, useEffectEvent } from 'react';
import { AuthLoadingStatus } from 'store/slices/createAuthLoadingSlice';
import { usePersistStore } from 'store/usePersistStore';
import { useHandleActionsAfterLogin } from 'utils/auth/useLogin';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const SocialLoginPage: FC<ServerSidePropsType> = () => {
    const { query } = useRouter();
    const updateAuthLoadingState = usePersistStore((store) => store.updateAuthLoadingState);
    const updateUserEntryState = usePersistStore((store) => store.updateUserEntryState);
    const handleActionsAfterLogin = useHandleActionsAfterLogin();
    const router = useRouter();

    const onSocialLogin = useEffectEvent(() => {
        const replaceUrl = getStringFromUrlQuery(query.redirect ?? '/');

        if (query.exceptionType === 'socialNetworkLoginException') {
            const authLoadingStatus: AuthLoadingStatus = {
                authLoadingStatus: 'social-login-fail',
                socialNetworkType: getStringFromUrlQuery(query.socialNetwork),
            };
            updateAuthLoadingState(authLoadingStatus);
            router.replace(replaceUrl).then(() => router.reload());
        } else {
            handleActionsAfterLogin(query.showCartMergeInfo === 'true', replaceUrl);
            updateUserEntryState(query.isRegistration === 'true' ? 'registration' : 'login');
        }
    });

    useEffect(() => {
        onSocialLogin();
    }, []);

    return null;
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default SocialLoginPage;
