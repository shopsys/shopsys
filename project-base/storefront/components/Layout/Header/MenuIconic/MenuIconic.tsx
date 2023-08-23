import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useComparison } from 'hooks/comparison/useComparison';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useWishlist } from 'hooks/useWishlist';
import { MenuIconicItem, MenuIconicItemLink } from './MenuIconicElements';
import { MenuIconicItemUser } from './MenuIconicItemUser';
import { Compare, Heart, Marker } from 'components/Basic/Icon/IconsSvg';

const TEST_IDENTIFIER = 'layout-header-menuiconic';

export const MenuIconic: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [storesUrl, productComparisonUrl, wishlistUrl] = getInternationalizedStaticUrls(
        ['/stores', '/product-comparison', '/wishlist'],
        url,
    );
    const { comparison } = useComparison();
    const { wishlist } = useWishlist();

    return (
        <ul className="flex" data-testid={TEST_IDENTIFIER}>
            <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-stores'} className="max-lg:hidden">
                <MenuIconicItemLink href={storesUrl}>
                    <Marker className="mr-2 w-4 text-white" />
                    {t('Stores')}
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-login'} className="max-lg:mr-0">
                <MenuIconicItemUser />
            </MenuIconicItem>
            <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-comparison'} className="max-lg:hidden">
                <MenuIconicItemLink href={productComparisonUrl} title={t('Comparison')}>
                    <Compare className="mr-2 w-4 text-white" />
                    {!!comparison?.products.length && <span>{comparison.products.length}</span>}
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-wishlist'} className="max-lg:hidden">
                <MenuIconicItemLink href={wishlistUrl} title={t('Wishlist')}>
                    <Heart isFull={!!wishlist?.products.length} className="mr-2 w-4 text-white" />
                    {!!wishlist?.products.length && <span>{wishlist.products.length}</span>}
                </MenuIconicItemLink>
            </MenuIconicItem>
        </ul>
    );
};
