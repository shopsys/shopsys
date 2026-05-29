import { Webline } from 'components/Layout/Webline/Webline';
import { useNavigationQuery } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';
import { Navigation } from './Navigation';

const NavigationPlaceholder = dynamic(() =>
    import('./NavigationPlaceholder').then((component) => component.NavigationPlaceholder),
);

export const DeferredNavigation: FC = () => {
    const [{ data: navigationData, fetching: isNavigationFetching }] = useNavigationQuery();
    const shouldRender = useDeferredRender('navigation');

    if (!navigationData?.navigation.length) {
        return isNavigationFetching ? (
            <Webline className="relative">
                <NavigationPlaceholder />
            </Webline>
        ) : null;
    }

    return (
        <Webline className="relative">
            {shouldRender ? (
                <Navigation navigation={navigationData.navigation} />
            ) : (
                <NavigationPlaceholder navigation={navigationData.navigation} />
            )}
        </Webline>
    );
};
