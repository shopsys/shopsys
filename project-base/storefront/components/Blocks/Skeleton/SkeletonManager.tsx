import { SkeletonPageArticle } from './SkeletonPageArticle';
import { SkeletonPageBlogCategory } from './SkeletonPageBlogCategory';
import { SkeletonPageProductDetail } from './SkeletonPageProductDetail';
import { SkeletonPageProductsList } from './SkeletonPageProductsList';
import { SkeletonPageProductsListSimple } from './SkeletonPageProductsListSimple';
import { SkeletonPageStore } from './SkeletonPageStore';
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
            // TODO add proper skeleton
            return <SkeletonPageArticle />;
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
            // TODO add proper skeleton
            return <SkeletonPageArticle />;
        case 'store':
            return <SkeletonPageStore />;
        default:
            return null;
    }
};
