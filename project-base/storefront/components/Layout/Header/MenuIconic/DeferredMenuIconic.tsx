import dynamic from 'next/dynamic';
import { useEffect, useState } from 'react';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useDeferredRender } from 'utils/useDeferredRender';
import { MenuIconicPlaceholder } from './MenuIconicPlaceholder';

const MenuIconic = dynamic(() => import('./MenuIconic').then((component) => component.MenuIconic), {
    ssr: false,
    loading: () => <MenuIconicPlaceholder />,
});

type DeferredMenuIconicProps = {
    isDesktop: boolean | undefined;
};

export const DeferredMenuIconic: FC<DeferredMenuIconicProps> = ({ isDesktop }) => {
    const shouldRender = useDeferredRender('menu_iconic');
    const isUserLoggedIn = useIsUserLoggedIn();
    const [hasRenderedMenu, setHasRenderedMenu] = useState(false);

    useEffect(() => {
        if (shouldRender && isDesktop) {
            setHasRenderedMenu(true);
        }
    }, [isDesktop, shouldRender]);

    // Keep the dialog's return-focus target mounted while CSS hides the header on mobile.
    return shouldRender && (isDesktop || (hasRenderedMenu && !isUserLoggedIn)) ? (
        <MenuIconic />
    ) : (
        <MenuIconicPlaceholder />
    );
};
