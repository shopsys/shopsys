import { Webline } from 'components/Layout/Webline/Webline';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';
import type { NavigationProps } from './Navigation';
import { Navigation } from './Navigation';

const NavigationPlaceholder = dynamic(() =>
    import('./NavigationPlaceholder').then((component) => component.NavigationPlaceholder),
);

type DeferredNavigationProps = {
    isNavigationFetching?: boolean;
    navigation?: NavigationProps['navigation'];
};

export const DeferredNavigation: FC<DeferredNavigationProps> = ({ isNavigationFetching, navigation }) => {
    const shouldRender = useDeferredRender('navigation');

    if (!navigation?.length) {
        return isNavigationFetching ? (
            <Webline className="relative">
                <NavigationPlaceholder />
            </Webline>
        ) : null;
    }

    return (
        <Webline className="relative">
            {shouldRender ? <Navigation navigation={navigation} /> : <NavigationPlaceholder navigation={navigation} />}
        </Webline>
    );
};
