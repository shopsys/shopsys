import { SkeletonPageArticle } from './SkeletonPageArticle';
import { SkeletonPageBlogArticle } from './SkeletonPageBlogArticle';
import { SkeletonPageBlogCategory } from './SkeletonPageBlogCategory';
import { SkeletonPageCart } from './SkeletonPageCart';
import { SkeletonPageComparison } from './SkeletonPageComparison';
import { SkeletonPageConfirmation } from './SkeletonPageConfirmation';
import { SkeletonPageContactInformation } from './SkeletonPageContactInformation';
import { SkeletonPageHome } from './SkeletonPageHome';
import { SkeletonPageOrder } from './SkeletonPageOrder';
import { SkeletonPageOrders } from './SkeletonPageOrders';
import { SkeletonPageProductDetail } from './SkeletonPageProductDetail';
import { SkeletonPageProductDetailMainVariant } from './SkeletonPageProductDetailMainVariant';
import { SkeletonPageProductsList } from './SkeletonPageProductsList';
import { SkeletonPageProductsListSimple } from './SkeletonPageProductsListSimple';
import { SkeletonPageStore } from './SkeletonPageStore';
import { SkeletonPageStores } from './SkeletonPageStores';
import { SkeletonPageTransportAndPayment } from './SkeletonPageTransportAndPayment';
import { SkeletonPageWishlist } from './SkeletonPageWishlist';
import { useEffect } from 'react';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { useSessionStore } from 'store/useSessionStore';

type SkeletonManagerProps = {
    isFetchingData?: boolean;
    isPageLoading: boolean;
    pageTypeOverride?: PageType;
};

export const SkeletonManager: FC<SkeletonManagerProps> = ({
    isFetchingData,
    isPageLoading,
    children,
    pageTypeOverride,
}) => {
    const redirectPageType = useSessionStore((s) => s.redirectPageType);
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const pageType = redirectPageType ?? pageTypeOverride;

    useEffect(() => {
        if (pageTypeOverride) {
            updatePageLoadingState({ redirectPageType: pageTypeOverride });
        }
    }, [pageTypeOverride]);

    useEffect(() => {
        if (isPageLoading) {
            window.scrollTo({ top: 0 });
        }
    }, [isPageLoading]);

    if (!isPageLoading && !isFetchingData) {
        return <>{children}</>;
    }

    switch (pageType) {
        case 'homepage':
            return <SkeletonPageHome />;
        case 'product':
            return <SkeletonPageProductDetail />;
        case 'productMainVariant':
            return <SkeletonPageProductDetailMainVariant />;
        case 'category':
        case 'seo_category':
            return <SkeletonPageProductsList />;
        case 'brand':
        case 'flag':
            return <SkeletonPageProductsListSimple />;
        case 'article':
            return <SkeletonPageArticle />;
        case 'blogArticle':
            return <SkeletonPageBlogArticle />;
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
        case 'transport-and-payment':
            return <SkeletonPageTransportAndPayment />;
        case 'contact-information':
            return <SkeletonPageContactInformation />;
        case 'cart':
            return <SkeletonPageCart />;
        case 'order-confirmation':
            return <SkeletonPageConfirmation />;
        default:
            return null;
    }
};
