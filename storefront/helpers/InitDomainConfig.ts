import { GetServerSidePropsContext } from 'next';
import { AppStore } from 'redux/main';
import { domainActions } from 'redux/slices/domain';
import { getDomainConfig } from 'utils/Domain/Domain';

export const initDomainConfig = (context: GetServerSidePropsContext, store: AppStore): void => {
    const domain = context.req.headers.host;
    const domainConfig = getDomainConfig(domain);
    store.dispatch(domainActions.setDomain(domainConfig));
};
