import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useComparison } from 'hooks/comparison/useComparison';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useWishlist } from 'hooks/useWishlist';
import { MenuIconicItem, MenuIconicItemLink, MenuIconicItemIcon } from './MenuIconicElements';
import { MenuIconicItemUser } from './MenuIconicItemUser';

const TEST_IDENTIFIER = 'layout-header-menuiconic';

export const MenuIconic: FC = () => {
    const t = useTypedTranslationFunction();
    const { url } = useDomainConfig();
    const [storesUrl, productsComparisonUrl, wishlistUrl] = getInternationalizedStaticUrls(
        ['/stores', '/products-comparison', '/wishlist'],
        url,
    );
    const { comparison } = useComparison();
    const { wishlist } = useWishlist();

    return (
        <ul className="flex" data-testid={TEST_IDENTIFIER}>
            <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-stores'} className="max-lg:hidden">
                <MenuIconicItemLink href={storesUrl}>
                    <MenuIconicItemIcon icon="Marker" />
                    {t('Stores')}
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-login'} className="max-lg:mr-0">
                <MenuIconicItemUser />
            </MenuIconicItem>

            <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-comparison'} className="max-lg:hidden">
                <MenuIconicItemLink href={productsComparisonUrl} title={t('Comparison')}>
                    <MenuIconicItemIcon icon="Compare" />
                    {!!comparison?.products.length && <span>{comparison.products.length}</span>}
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-wishlist'} className="max-lg:hidden">
                <MenuIconicItemLink href={wishlistUrl} title={t('Wishlist')}>
                    <MenuIconicItemIcon icon={wishlist?.products.length ? 'HeartFull' : 'Heart'} />
                    {!!wishlist?.products.length && <span>{wishlist.products.length}</span>}
                </MenuIconicItemLink>
            </MenuIconicItem>
        </ul>
    );
};
