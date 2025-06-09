import { Navigation } from './Navigation';
import { Webline } from 'components/Layout/Webline/Webline';
import { useNavigationQuery } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const NavigationPlaceholder = dynamic(() =>
    import('./NavigationPlaceholder').then((component) => component.NavigationPlaceholder),
);

export const DeferredNavigation: FC = () => {
    const [{ data: navigationData }] = useNavigationQuery();
    const shouldRender = useDeferredRender('navigation');

    if (!navigationData?.navigation.length) {
        return null;
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
