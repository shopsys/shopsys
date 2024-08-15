import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import useTranslation from 'next-translate/useTranslation';
import { useCurrentCustomerUserPermissions } from 'utils/auth/useCurrentCustomerUserAuth';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type UserMenuItemType = {
    link: string;
    text: string;
    count?: number;
};

export const useUserMenuItems = (): UserMenuItemType[] => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const { comparison } = useComparison();
    const { wishlist } = useWishlist();
    const { canManageUsers } = useCurrentCustomerUserPermissions();
    const [customerOrdersUrl, customerEditProfileUrl, productComparisonUrl, wishlistUrl, customerUsersUrl] =
        getInternationalizedStaticUrls(
            ['/customer/orders', '/customer/edit-profile', '/product-comparison', '/wishlist', '/customer/users'],
            url,
        );

    const userMenuItems: UserMenuItemType[] = [
        {
            text: t('Edit profile'),
            link: customerEditProfileUrl,
        },
        {
            text: t('Orders'),
            link: customerOrdersUrl,
        },
        {
            text: t('Wishlist'),
            link: wishlistUrl,
            count: wishlist?.products.length,
        },
        {
            text: t('Comparison'),
            link: productComparisonUrl,
            count: comparison?.products.length,
        },
    ];

    if (!canManageUsers) {
        return userMenuItems;
    }

    return [...userMenuItems, {
        text: t('Customer users'),
        link: customerUsersUrl,
    }];
};
