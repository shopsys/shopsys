import { SkeletonPageArticle } from './SkeletonPageArticle';
import { SkeletonPageBlogArticle } from './SkeletonPageBlogArticle';
import { SkeletonPageBlogCategory } from './SkeletonPageBlogCategory';
import { SkeletonPageBrand } from './SkeletonPageBrand';
import { SkeletonPageBrandsOverview } from './SkeletonPageBrandsOverview';
import { SkeletonPageCart } from './SkeletonPageCart';
import { SkeletonPageComparison } from './SkeletonPageComparison';
import { SkeletonPageConfirmation } from './SkeletonPageConfirmation';
import { SkeletonPageContact } from './SkeletonPageContact';
import { SkeletonPageContactInformation } from './SkeletonPageContactInformation';
import { SkeletonPageCustomerChangePassword } from './SkeletonPageCustomerChangePassword';
import { SkeletonPageCustomerComplaintDetail } from './SkeletonPageCustomerComplaintDetail';
import { SkeletonPageCustomerComplaintList } from './SkeletonPageCustomerComplaintList';
import { SkeletonPageCustomerComplaintNew } from './SkeletonPageCustomerComplaintNew';
import { SkeletonPageCustomerEditProfile } from './SkeletonPageCustomerEditProfile';
import { SkeletonPageCustomerOrderDetail } from './SkeletonPageCustomerOrderDetail';
import { SkeletonPageCustomerOrderList } from './SkeletonPageCustomerOrderList';
import { SkeletonPageCustomerUsers } from './SkeletonPageCustomerUsers';
import { SkeletonPageFlag } from './SkeletonPageFlag';
import { SkeletonPageForgotPassword } from './SkeletonPageForgotPassword';
import { SkeletonPageHome } from './SkeletonPageHome';
import { SkeletonPageLogin } from './SkeletonPageLogin';
import { SkeletonPageProductDetail } from './SkeletonPageProductDetail';
import { SkeletonPageProductDetailMainVariant } from './SkeletonPageProductDetailMainVariant';
import { SkeletonPageProductsList } from './SkeletonPageProductsList';
import { SkeletonPageRegistration } from './SkeletonPageRegistration';
import { SkeletonPageStore } from './SkeletonPageStore';
import { SkeletonPageStores } from './SkeletonPageStores';
import { SkeletonPageTransportAndPayment } from './SkeletonPageTransportAndPayment';
import { SkeletonPageUserConsent } from './SkeletonPageUserConsent';
import { SkeletonPageWishlist } from './SkeletonPageWishlist';
import { useEffect } from 'react';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { useSessionStore } from 'store/useSessionStore';
import { SkeletonEnum } from 'types/skeletons';

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
        case SkeletonEnum.Article:
            return <SkeletonPageArticle />;
        case SkeletonEnum.BlogArticle:
            return <SkeletonPageBlogArticle />;
        case SkeletonEnum.BlogCategory:
            return <SkeletonPageBlogCategory />;
        case SkeletonEnum.Brand:
            return <SkeletonPageBrand />;
        case SkeletonEnum.BrandsOverview:
            return <SkeletonPageBrandsOverview />;
        case SkeletonEnum.Cart:
            return <SkeletonPageCart />;
        case SkeletonEnum.Category:
            return <SkeletonPageProductsList />;
        case SkeletonEnum.Comparison:
            return <SkeletonPageComparison />;
        case SkeletonEnum.Contact:
            return <SkeletonPageContact />;
        case SkeletonEnum.ContactInformation:
            return <SkeletonPageContactInformation />;
        case SkeletonEnum.ComplaintNew:
            return <SkeletonPageCustomerComplaintNew />;
        case SkeletonEnum.ComplaintDetail:
            return <SkeletonPageCustomerComplaintDetail />;
        case SkeletonEnum.ComplaintList:
            return <SkeletonPageCustomerComplaintList />;
        case SkeletonEnum.CustomerUsers:
            return <SkeletonPageCustomerUsers />;
        case SkeletonEnum.OrderList:
            return <SkeletonPageCustomerOrderList />;
        case SkeletonEnum.OrderDetail:
            return <SkeletonPageCustomerOrderDetail />;
        case SkeletonEnum.EditProfile:
            return <SkeletonPageCustomerEditProfile />;
        case SkeletonEnum.ChangePassword:
            return <SkeletonPageCustomerChangePassword />;
        case SkeletonEnum.Flag:
            return <SkeletonPageFlag />;
        case SkeletonEnum.ForgotPassword:
            return <SkeletonPageForgotPassword />;
        case SkeletonEnum.Homepage:
            return <SkeletonPageHome />;
        case SkeletonEnum.Login:
            return <SkeletonPageLogin />;
        case SkeletonEnum.OrderConfirmation:
            return <SkeletonPageConfirmation />;
        case SkeletonEnum.Product:
            return <SkeletonPageProductDetail />;
        case SkeletonEnum.ProductMainVariant:
            return <SkeletonPageProductDetailMainVariant />;
        case SkeletonEnum.Registration:
            return <SkeletonPageRegistration />;
        case SkeletonEnum.SeoCategory:
            return <SkeletonPageProductsList />;
        case SkeletonEnum.Store:
            return <SkeletonPageStore />;
        case SkeletonEnum.Stores:
            return <SkeletonPageStores />;
        case SkeletonEnum.TransportAndPayment:
            return <SkeletonPageTransportAndPayment />;
        case SkeletonEnum.Wishlist:
            return <SkeletonPageWishlist />;
        case SkeletonEnum.UserConsent:
            return <SkeletonPageUserConsent />;
        default:
            return null;
    }
};
