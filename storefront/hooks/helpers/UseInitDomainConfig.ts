import { DomainConfigType, getDomainConfig } from 'utils/Domain/Domain';
import { domainActions } from 'redux/slices/domain';
import { useShopsysDispatch } from 'redux/main';

export const useInitDomainConfig = (domainConfig: DomainConfigType): void => {
    const dispatch = useShopsysDispatch();

    let selectedDomain;
    if (domainConfig !== null) {
        selectedDomain = new URL(domainConfig?.url as string).host;
    }
    dispatch(domainActions.setDomain(getDomainConfig(selectedDomain)));
};
