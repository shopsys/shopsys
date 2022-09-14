import { DomainConfigType, getDomainConfig } from 'helpers/domain/domain';
import { GetServerSidePropsContext } from 'next';
import { AppStore } from 'redux/main';
import { domainActions } from 'redux/slices/domain';

export const initDomainConfig = (context: GetServerSidePropsContext, store: AppStore): DomainConfigType => {
    const domain = context.req.headers.host;
    const domainConfig = getDomainConfig(domain);
    store.dispatch(domainActions.setDomain(domainConfig));

    return domainConfig;
};
