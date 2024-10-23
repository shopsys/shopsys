import { SkeletonModuleCartInHeader } from 'components/Blocks/Skeleton/SkeletonModuleCartInHeader';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const CartInHeader = dynamic(() => import('./CartInHeader').then((component) => ({
    default: component.CartInHeader
})), {
    ssr: false,
    loading: () => <SkeletonModuleCartInHeader />,
});

export const DeferredCartInHeader: FC = () => {
    const shouldRender = useDeferredRender('cart_in_header');

    return shouldRender ? <CartInHeader className="order-3 vl:order-4" /> : <SkeletonModuleCartInHeader />;
};
