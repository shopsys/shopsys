import { AppStore, useShopsysDispatch } from 'redux/main';
import { domainActions } from 'redux/slices/domain';
import { getDomainConfig } from 'utils/Domain/Domain';
import { GetServerSidePropsContext } from 'next';

export const initDomainConfig = (context: GetServerSidePropsContext, store: AppStore): void => {
    const domain = context.req.headers.host;
    const domainConfig = getDomainConfig(domain);
    store.dispatch(domainActions.setDomain(domainConfig));
};

export const useInitDomainConfigOnClient = (): void => {
    const dispatch = useShopsysDispatch();
    if (typeof window !== 'undefined') {
        const domainConfig = getDomainConfig(window.location.host);
        dispatch(domainActions.setDomain(domainConfig));
    }
};
