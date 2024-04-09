import { SkeletonModuleCart } from 'components/Blocks/Skeleton/SkeletonModuleCart';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const Cart = dynamic(() => import('./Cart').then((component) => component.Cart), {
    ssr: false,
    loading: () => <SkeletonModuleCart />,
});

export const DeferredCart: FC = () => {
    const shouldRender = useDeferredRender('cart_in_header');

    return shouldRender ? <Cart className="order-3 vl:order-4" /> : <SkeletonModuleCart />;
};
