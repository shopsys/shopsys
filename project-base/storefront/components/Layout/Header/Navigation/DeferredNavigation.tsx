import { Navigation } from './Navigation';
import { DEFAULT_SKELETON_TYPE } from 'config/constants';
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

    return shouldRender ? (
        <Navigation navigation={navigationData.navigation} skeletonType={DEFAULT_SKELETON_TYPE} />
    ) : (
        <NavigationPlaceholder navigation={navigationData.navigation} skeletonType={DEFAULT_SKELETON_TYPE} />
    );
};
