import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const MenuIconicPlaceholder = dynamic(() =>
    import('./MenuIconicPlaceholder').then((component) => component.MenuIconicPlaceholder),
);

const MenuIconic = dynamic(() => import('./MenuIconic').then((component) => component.MenuIconic), {
    ssr: false,
    loading: () => <MenuIconicPlaceholder />,
});

export const DeferredMenuIconic: FC = () => {
    const shouldRender = useDeferredRender('menu_iconic');

    return shouldRender ? <MenuIconic /> : <MenuIconicPlaceholder />;
};
