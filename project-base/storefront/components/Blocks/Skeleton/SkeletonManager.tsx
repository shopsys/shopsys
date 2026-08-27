import { ComponentType, useEffect } from 'react';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { useSessionStore } from 'store/useSessionStore';
import { SkeletonEnum } from 'types/skeletons';
import { SkeletonPageArticle } from './SkeletonPageArticle';
import { SkeletonPageBlogArticle } from './SkeletonPageBlogArticle';
import { SkeletonPageBlogCategory } from './SkeletonPageBlogCategory';
import { SkeletonPageBrand } from './SkeletonPageBrand';
import { SkeletonPageBrandsOverview } from './SkeletonPageBrandsOverview';
import { SkeletonPageCart } from './SkeletonPageCart';
import { SkeletonPageCatalog } from './SkeletonPageCatalog';
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
import { SkeletonPageHome } from './SkeletonPageHome';
import { SkeletonPageLogin } from './SkeletonPageLogin';
import { SkeletonPageOrderWithdrawal } from './SkeletonPageOrderWithdrawal';
import { SkeletonPageOrderWithdrawalConfirmation } from './SkeletonPageOrderWithdrawalConfirmation';
import { SkeletonPageOrderWithdrawalSuccess } from './SkeletonPageOrderWithdrawalSuccess';
import { SkeletonPageProductDetail } from './SkeletonPageProductDetail';
import { SkeletonPageProductDetailMainVariant } from './SkeletonPageProductDetailMainVariant';
import { SkeletonPageProductsList } from './SkeletonPageProductsList';
import { SkeletonPageRegistration } from './SkeletonPageRegistration';
import { SkeletonPageResetPassword } from './SkeletonPageResetPassword';
import { SkeletonPageSearch } from './SkeletonPageSearch';
import { SkeletonPageStore } from './SkeletonPageStore';
import { SkeletonPageStores } from './SkeletonPageStores';
import { SkeletonPageTransportAndPayment } from './SkeletonPageTransportAndPayment';
import { SkeletonPageUserConsent } from './SkeletonPageUserConsent';
import { SkeletonPageWishlist } from './SkeletonPageWishlist';

const SKELETON_COMPONENT_MAP: Record<PageType, ComponentType> = {
    [SkeletonEnum.Article]: SkeletonPageArticle,
    [SkeletonEnum.BlogArticle]: SkeletonPageBlogArticle,
    [SkeletonEnum.BlogCategory]: SkeletonPageBlogCategory,
    [SkeletonEnum.Brand]: SkeletonPageBrand,
    [SkeletonEnum.BrandsOverview]: SkeletonPageBrandsOverview,
    [SkeletonEnum.Cart]: SkeletonPageCart,
    [SkeletonEnum.Catalog]: SkeletonPageCatalog,
    [SkeletonEnum.Category]: SkeletonPageProductsList,
    [SkeletonEnum.ChangePassword]: SkeletonPageCustomerChangePassword,
    [SkeletonEnum.Comparison]: SkeletonPageComparison,
    [SkeletonEnum.ComplaintDetail]: SkeletonPageCustomerComplaintDetail,
    [SkeletonEnum.ComplaintList]: SkeletonPageCustomerComplaintList,
    [SkeletonEnum.ComplaintNew]: SkeletonPageCustomerComplaintNew,
    [SkeletonEnum.Contact]: SkeletonPageContact,
    [SkeletonEnum.ContactInformation]: SkeletonPageContactInformation,
    [SkeletonEnum.CustomerUsers]: SkeletonPageCustomerUsers,
    [SkeletonEnum.EditProfile]: SkeletonPageCustomerEditProfile,
    [SkeletonEnum.Flag]: SkeletonPageFlag,
    [SkeletonEnum.Homepage]: SkeletonPageHome,
    [SkeletonEnum.Login]: SkeletonPageLogin,
    [SkeletonEnum.OrderConfirmation]: SkeletonPageConfirmation,
    [SkeletonEnum.OrderDetail]: SkeletonPageCustomerOrderDetail,
    [SkeletonEnum.OrderList]: SkeletonPageCustomerOrderList,
    [SkeletonEnum.OrderWithdrawal]: SkeletonPageOrderWithdrawal,
    [SkeletonEnum.OrderWithdrawalConfirmation]: SkeletonPageOrderWithdrawalConfirmation,
    [SkeletonEnum.OrderWithdrawalSuccess]: SkeletonPageOrderWithdrawalSuccess,
    [SkeletonEnum.Product]: SkeletonPageProductDetail,
    [SkeletonEnum.ProductMainVariant]: SkeletonPageProductDetailMainVariant,
    [SkeletonEnum.Registration]: SkeletonPageRegistration,
    [SkeletonEnum.ResetPassword]: SkeletonPageResetPassword,
    [SkeletonEnum.Search]: SkeletonPageSearch,
    [SkeletonEnum.SeoCategory]: SkeletonPageProductsList,
    [SkeletonEnum.Store]: SkeletonPageStore,
    [SkeletonEnum.Stores]: SkeletonPageStores,
    [SkeletonEnum.TransportAndPayment]: SkeletonPageTransportAndPayment,
    [SkeletonEnum.UserConsent]: SkeletonPageUserConsent,
    [SkeletonEnum.Wishlist]: SkeletonPageWishlist,
};

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
    const hadClientSideNavigation = useSessionStore((s) => s.hadClientSideNavigation);
    const pageType = redirectPageType ?? pageTypeOverride;

    useEffect(() => {
        if (pageTypeOverride) {
            updatePageLoadingState({ redirectPageType: pageTypeOverride });
        }
    }, [pageTypeOverride, updatePageLoadingState]);

    useEffect(() => {
        if (isPageLoading) {
            window.scrollTo({ top: 0 });
        }
    }, [isPageLoading]);

    if (!isPageLoading && !isFetchingData) {
        return <div className={hadClientSideNavigation ? 'animate-in' : undefined}>{children}</div>;
    }

    const SkeletonComponent = pageType ? SKELETON_COMPONENT_MAP[pageType] : null;

    return SkeletonComponent ? <SkeletonComponent /> : children;
};
