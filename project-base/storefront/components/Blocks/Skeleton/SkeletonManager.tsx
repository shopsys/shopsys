import { SkeletonPageArticle } from './SkeletonPageArticle';
import { SkeletonPageBlogCategory } from './SkeletonPageBlogCategory';
import { SkeletonPageComparison } from './SkeletonPageComparison';
import { SkeletonPageHome } from './SkeletonPageHome';
import { SkeletonPageOrder } from './SkeletonPageOrder';
import { SkeletonPageOrders } from './SkeletonPageOrders';
import { SkeletonPageProductDetail } from './SkeletonPageProductDetail';
import { SkeletonPageProductsList } from './SkeletonPageProductsList';
import { SkeletonPageProductsListSimple } from './SkeletonPageProductsListSimple';
import { SkeletonPageStore } from './SkeletonPageStore';
import { SkeletonPageStores } from './SkeletonPageStores';
import { SkeletonPageWishlist } from './SkeletonPageWishlist';
import { useEffect } from 'react';
import { useSessionStore } from 'store/useSessionStore';

type SkeletonManagerProps = {
    isFetchingData?: boolean;
    isPageLoading: boolean;
};

export const SkeletonManager: FC<SkeletonManagerProps> = ({ isFetchingData, isPageLoading, children }) => {
    const redirectPageType = useSessionStore((s) => s.redirectPageType);

    useEffect(() => {
        if (isPageLoading) {
            window.scrollTo({ top: 0 });
        }
    }, [isPageLoading]);

    if (!isPageLoading && !isFetchingData) {
        return <>{children}</>;
    }

    switch (redirectPageType) {
        case 'homepage':
            return <SkeletonPageHome />;
        case 'product':
            return <SkeletonPageProductDetail />;
        case 'category':
            return <SkeletonPageProductsList />;
        case 'brand':
        case 'flag':
            return <SkeletonPageProductsListSimple />;
        case 'article':
        case 'blogArticle':
            return <SkeletonPageArticle />;
        case 'blogCategory':
            return <SkeletonPageBlogCategory />;
        case 'stores':
            return <SkeletonPageStores />;
        case 'store':
            return <SkeletonPageStore />;
        case 'wishlist':
            return <SkeletonPageWishlist />;
        case 'comparison':
            return <SkeletonPageComparison />;
        case 'orders':
            return <SkeletonPageOrders />;
        case 'order':
            return <SkeletonPageOrder />;
        default:
            return null;
    }
};
