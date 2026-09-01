import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useRouter } from 'next/router';
import { useEffect, useEffectEvent } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { storeAuthNotification } from 'utils/auth/authNotificationStorage';
import { getAllowedSocialNetworkType } from 'utils/auth/getAllowedSocialNetworkType';
import { performAuthHardNavigation } from 'utils/auth/performAuthHardNavigation';
import { useHandleActionsAfterLogin } from 'utils/auth/useLogin';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const SocialLoginPage: FC<ServerSidePropsType> = () => {
    const { query } = useRouter();
    const { domainId } = useDomainConfig();
    const updateUserEntryState = usePersistStore((store) => store.updateUserEntryState);
    const handleActionsAfterLogin = useHandleActionsAfterLogin();

    const onSocialLogin = useEffectEvent(() => {
        const replaceUrl = getStringFromUrlQuery(query.redirect ?? '/');

        if (query.exceptionType === 'socialNetworkLoginException') {
            const socialNetworkType = getAllowedSocialNetworkType(getStringFromUrlQuery(query.socialNetwork));
            storeAuthNotification(domainId, { type: 'social-login-fail', socialNetworkType });
            performAuthHardNavigation(replaceUrl);
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
