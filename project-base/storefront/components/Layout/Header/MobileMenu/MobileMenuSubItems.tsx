import { CountBadge } from 'components/Basic/CountBadge/CountBadge';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import { HeartIcon } from 'components/Basic/Icon/HeartIcon';
import { StoreIcon } from 'components/Basic/Icon/StoreIcon';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type SubMenuProps = {
    onNavigate: () => void;
};

export const SubMenu: FC<SubMenuProps> = ({ onNavigate }) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [storesUrl, productComparisonUrl, wishlistUrl] = getInternationalizedStaticUrls(
        ['/stores', '/product-comparison', '/wishlist'],
        url,
    );
    const { comparison } = useComparison();
    const { wishlist } = useWishlist();

    return (
        <div className="mt-auto">
            <div className="mx-5 mt-5 border-border-less border-t pt-5 pb-5">
                <p className="mb-3 font-secondary font-semibold text-text-less text-xs uppercase">{t('Quick links')}</p>

                <div className="flex flex-col gap-2">
                    <SubMenuItem
                        href={storesUrl}
                        icon={StoreIcon}
                        label={t('Stores')}
                        type="stores"
                        onClick={onNavigate}
                    />

                    <SubMenuItem
                        count={comparison?.products.length}
                        href={productComparisonUrl}
                        icon={CompareIcon}
                        label={t('Comparison')}
                        type="comparison"
                        onClick={onNavigate}
                    />

                    <SubMenuItem
                        count={wishlist?.products.length}
                        href={wishlistUrl}
                        icon={HeartIcon}
                        label={t('Wishlist')}
                        type="wishlist"
                        onClick={onNavigate}
                    />
                </div>
            </div>
        </div>
    );
};

type SubMenuItemProps = {
    count?: number;
    href: string;
    icon: SvgFC;
    label: string;
    type: PageType;
    onClick: () => void;
};

const SubMenuItem: FC<SubMenuItemProps> = ({ count, href, icon: Icon, label, onClick, type }) => {
    return (
        <ExtendedNextLink
            passHref
            aria-label={count ? `${label} (${count})` : label}
            className="flex min-h-12 items-center gap-3 rounded-md bg-background-more px-3 py-3 text-left font-secondary font-semibold text-base text-text-default no-underline transition-colors hover:bg-background-most hover:text-text-default hover:no-underline active:bg-background-most"
            href={href}
            type={type}
            onClick={onClick}
        >
            <span className="relative shrink-0">
                <Icon className="size-6" />
                {!!count && (
                    <CountBadge
                        aria-hidden="true"
                        className="absolute -top-2 -right-2 bg-icon-accent-red text-text-inverted"
                    >
                        {count}
                    </CountBadge>
                )}
            </span>

            {label}
        </ExtendedNextLink>
    );
};
