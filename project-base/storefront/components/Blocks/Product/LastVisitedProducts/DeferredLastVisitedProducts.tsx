import { LastVisitedProductsProps } from './LastVisitedProducts';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const LastVisitedProducts = dynamic(
    () => import('./LastVisitedProducts').then((component) => ({
        default: component.LastVisitedProducts
    })),
    {
        ssr: false,
    },
);

export const DeferredLastVisitedProducts: FC<LastVisitedProductsProps> = ({ currentProductCatnum }) => {
    const shouldRender = useDeferredRender('last_visited');

    return shouldRender ? <LastVisitedProducts currentProductCatnum={currentProductCatnum} /> : null;
};
