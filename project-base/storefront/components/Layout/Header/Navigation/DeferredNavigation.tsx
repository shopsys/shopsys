import { Webline } from 'components/Layout/Webline/Webline';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';
import type { NavigationProps } from './Navigation';
import { NavigationPlaceholder } from './NavigationPlaceholder';

const Navigation = dynamic<NavigationProps>(() => import('./Navigation').then((component) => component.Navigation), {
    ssr: false,
    loading: () => <NavigationPlaceholder />,
});

type DeferredNavigationProps = {
    isDesktop?: boolean;
    isNavigationFetching?: boolean;
    navigation?: NavigationProps['navigation'];
};

type DesktopDeferredNavigationProps = {
    navigation: NavigationProps['navigation'];
};

const DesktopDeferredNavigation: FC<DesktopDeferredNavigationProps> = ({ navigation }) => {
    const shouldRender = useDeferredRender('navigation');

    return (
        <Webline className="relative">
            {shouldRender ? <Navigation navigation={navigation} /> : <NavigationPlaceholder navigation={navigation} />}
        </Webline>
    );
};

export const DeferredNavigation: FC<DeferredNavigationProps> = ({ isDesktop, isNavigationFetching, navigation }) => {
    if (isDesktop === false) {
        return null;
    }

    if (!navigation?.length) {
        return isNavigationFetching ? (
            <Webline className="relative">
                <NavigationPlaceholder />
            </Webline>
        ) : null;
    }

    if (isDesktop === undefined) {
        return (
            <Webline className="relative">
                <NavigationPlaceholder navigation={navigation} />
            </Webline>
        );
    }

    return <DesktopDeferredNavigation navigation={navigation} />;
};
