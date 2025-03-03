import { SkeletonModuleCartInHeader } from 'components/Blocks/Skeleton/SkeletonModuleCartInHeader';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const CartInHeader = dynamic(() => import('./CartInHeader').then((component) => component.CartInHeader), {
    ssr: false,
    loading: () => <SkeletonModuleCartInHeader />,
});

export const DeferredCartInHeader: FC = () => {
    const { canCreateOrder } = useAuthorization();
    const shouldRender = useDeferredRender('cart_in_header');

    if (!canCreateOrder) {
        return null;
    }

    return shouldRender ? <CartInHeader className="vl:order-4 order-3" /> : <SkeletonModuleCartInHeader />;
};
