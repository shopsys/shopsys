import { DomainConfigType, getDomainConfig } from 'utils/Domain/Domain';
import { domainActions } from 'redux/store/DomainStore';
import { useShopsysDispatch } from 'redux/store';

export const useInitDomainConfig = (domainConfig: DomainConfigType): void => {
    const dispatch = useShopsysDispatch();

    let selectedDomain;
    if (domainConfig !== null) {
        selectedDomain = new URL(domainConfig?.url as string).host;
    }
    dispatch(domainActions.setDomain(getDomainConfig(selectedDomain)));
};
