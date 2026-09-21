import { SkeletonModuleCartInHeader } from 'components/Blocks/Skeleton/SkeletonModuleCartInHeader';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const CartInHeader = dynamic(() => import('./CartInHeader').then((component) => component.CartInHeader), {
    ssr: false,
    loading: () => <SkeletonModuleCartInHeader />,
});

type DeferredCartInHeaderProps = {
    isDesktop: boolean | undefined;
};

export const DeferredCartInHeader: FC<DeferredCartInHeaderProps> = ({ isDesktop }) => {
    const { canCreateOrder } = useAuthorization();
    const shouldRender = useDeferredRender('cart_in_header');

    if (!canCreateOrder) {
        return null;
    }

    return (
        <div className="order-3 vl:order-4 vl:flex hidden">
            {shouldRender && isDesktop ? <CartInHeader /> : <SkeletonModuleCartInHeader />}
        </div>
    );
};
