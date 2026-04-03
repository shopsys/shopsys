import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';
import { LastVisitedProductsProps } from './LastVisitedProducts';

const LastVisitedProducts = dynamic(
    () => import('./LastVisitedProducts').then((component) => component.LastVisitedProducts),
    {
        ssr: false,
    },
);

export const DeferredLastVisitedProducts: FC<LastVisitedProductsProps> = ({ currentProductCatnum }) => {
    const shouldRender = useDeferredRender('last_visited');

    return shouldRender ? <LastVisitedProducts currentProductCatnum={currentProductCatnum} /> : null;
};
