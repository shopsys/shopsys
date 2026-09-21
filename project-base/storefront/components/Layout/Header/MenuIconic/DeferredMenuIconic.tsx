import dynamic from 'next/dynamic';
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

    return shouldRender && isDesktop ? <MenuIconic /> : <MenuIconicPlaceholder />;
};
