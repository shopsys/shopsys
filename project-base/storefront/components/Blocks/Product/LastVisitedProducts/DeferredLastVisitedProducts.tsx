import dynamic from 'next/dynamic';
import { useCookiesStore } from 'store/useCookiesStore';
import { useDeferredRender } from 'utils/useDeferredRender';
import type { LastVisitedProductsProps } from './LastVisitedProducts';

const LastVisitedProducts = dynamic(
    () => import('./LastVisitedProducts').then((component) => component.LastVisitedProducts),
    {
        ssr: false,
    },
);

export const DeferredLastVisitedProducts: FC<LastVisitedProductsProps> = ({ currentProductCatnum }) => {
    const hasLastVisitedProducts = useCookiesStore(
        (state) => state.lastVisitedProductsCatnums?.some((catnum) => catnum !== currentProductCatnum) ?? false,
    );
    const shouldRender = useDeferredRender('last_visited');

    return shouldRender && hasLastVisitedProducts ? (
        <LastVisitedProducts currentProductCatnum={currentProductCatnum} />
    ) : null;
};
