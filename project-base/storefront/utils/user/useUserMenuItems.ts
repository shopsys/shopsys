import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import useTranslation from 'next-translate/useTranslation';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { useCurrentCustomerUserPermissions } from 'utils/auth/useCurrentCustomerUserPermissions';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type UserMenuItemType = {
    link: string;
    text: string;
    count?: number;
    type?: PageType;
};

export const useUserMenuItems = (): UserMenuItemType[] => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const { comparison } = useComparison();
    const { wishlist } = useWishlist();
    const { canManageUsers } = useCurrentCustomerUserPermissions();
    const [
        customerOrdersUrl,
        customerComplaintsUrl,
        customerUsersUrl,
        customerEditProfileUrl,
        productComparisonUrl,
        wishlistUrl,
    ] = getInternationalizedStaticUrls(
        [
            '/customer/orders',
            '/customer/complaints',
            '/customer/users',
            '/customer/edit-profile',
            '/product-comparison',
            '/wishlist',
        ],
        url,
    );

    const userMenuItems: UserMenuItemType[] = [
        {
            text: t('Edit profile'),
            link: customerEditProfileUrl,
            type: 'editProfile',
        },
        {
            text: t('Orders'),
            link: customerOrdersUrl,
            type: 'orderList',
        },
        {
            text: t('Complaints'),
            link: customerComplaintsUrl,
            type: 'complaintList',
        },
        {
            text: t('Wishlist'),
            link: wishlistUrl,
            count: wishlist?.products.length,
            type: 'wishlist',
        },
        {
            text: t('Comparison'),
            link: productComparisonUrl,
            count: comparison?.products.length,
            type: 'comparison',
        },
    ];

    if (!canManageUsers) {
        return userMenuItems;
    }

    return [
        ...userMenuItems,
        {
            text: t('Customer users'),
            link: customerUsersUrl,
        },
    ];
};
