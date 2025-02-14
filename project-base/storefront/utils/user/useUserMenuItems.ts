import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import { ComplaintsIcon } from 'components/Basic/Icon/ComplaintsIcon';
import { EditIcon } from 'components/Basic/Icon/EditIcon';
import { HeartIcon } from 'components/Basic/Icon/HeartIcon';
import { LockCheckIcon } from 'components/Basic/Icon/LockCheckIcon';
import { SearchListIcon } from 'components/Basic/Icon/SearchListIcon';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import useTranslation from 'next-translate/useTranslation';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useUserProfileSectionLabel } from 'utils/user/useUserProfileSectionLabel';

type UserMenuItemType = {
    link: string;
    text: string;
    count?: number;
    iconComponent?: React.ElementType;
    type?: PageType;
};

export const useUserMenuItems = (): UserMenuItemType[] => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const { comparison } = useComparison();
    const { wishlist } = useWishlist();
    const { canManageUsers, canCreateOrder, canViewCompanyOrders, canCreateComplaint, canViewCompanyComplaints } =
        useAuthorization();
    const [
        customerOrdersUrl,
        customerComplaintsUrl,
        customerUsersUrl,
        customerEditProfileUrl,
        customerChangePasswordUrl,
        productComparisonUrl,
        wishlistUrl,
    ] = getInternationalizedStaticUrls(
        [
            '/customer/orders',
            '/customer/complaints',
            '/customer/users',
            '/customer/edit-profile',
            '/customer/change-password',
            '/product-comparison',
            '/wishlist',
        ],
        url,
    );
    const userProfileSectionLabel = useUserProfileSectionLabel();

    const userMenuItems: UserMenuItemType[] = [];

    if (canCreateOrder || canViewCompanyOrders) {
        userMenuItems.push({
            text: t('Orders'),
            link: customerOrdersUrl,
            type: 'orderList',
            iconComponent: SearchListIcon,
        });
    }

    if (canCreateComplaint || canViewCompanyComplaints) {
        userMenuItems.push({
            text: t('Complaints'),
            link: customerComplaintsUrl,
            type: 'complaintList',
            iconComponent: ComplaintsIcon,
        });
    }

    if (canManageUsers) {
        userMenuItems.push({
            text: t('Customer users'),
            link: customerUsersUrl,
            type: 'customer-users',
            iconComponent: UserIcon,
        });
    }

    userMenuItems.push({
        text: userProfileSectionLabel,
        link: customerEditProfileUrl,
        type: 'editProfile',
        iconComponent: EditIcon,
    });

    userMenuItems.push({
        text: t('Change password'),
        link: customerChangePasswordUrl,
        type: 'changePassword',
        iconComponent: LockCheckIcon,
    });

    userMenuItems.push({
        text: t('Wishlist'),
        link: wishlistUrl,
        count: wishlist?.products.length,
        type: 'wishlist',
        iconComponent: HeartIcon,
    });

    userMenuItems.push({
        text: t('Comparison'),
        link: productComparisonUrl,
        count: comparison?.products.length,
        type: 'comparison',
        iconComponent: CompareIcon,
    });

    return userMenuItems;
};
