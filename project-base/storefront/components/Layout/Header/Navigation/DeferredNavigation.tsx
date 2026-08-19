import { Webline } from 'components/Layout/Webline/Webline';
import { lazy, Suspense } from 'react';
import { useDeferredRender } from 'utils/useDeferredRender';
import type { NavigationProps } from './Navigation';
import { NavigationPlaceholder } from './NavigationPlaceholder';

const Navigation = lazy(() =>
    import('./Navigation').then((component) => ({
        default: component.Navigation,
    })),
);

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
            {shouldRender ? (
                <Suspense fallback={<NavigationPlaceholder navigation={navigation} />}>
                    <Navigation navigation={navigation} />
                </Suspense>
            ) : (
                <NavigationPlaceholder navigation={navigation} />
            )}
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
