import { MenuIconicItem, MenuIconicItemLink } from './MenuIconicElements';
import MenuIconicItemUserAuthentication from './MenuIconicItemUserAuthentication';
import { getInternationalizedStaticUrlsServer } from 'app/_utils/staticUrls/getInternationalizedStaticUrlsServer';
import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import { HeartIcon } from 'components/Basic/Icon/HeartIcon';
import { MarkerIcon } from 'components/Basic/Icon/MarkerIcon';
import { getServerT } from 'utils/getServerTranslation';

export async function MenuIconic() {
    const t = await getServerT();
    const [storesUrl, productComparisonUrl, wishlistUrl] = getInternationalizedStaticUrlsServer([
        '/stores',
        '/product-comparison',
        '/wishlist',
    ]);
    // TODO: wishlist and comparison hooks
    // const { comparison } = useComparison();
    // const { wishlist } = useWishlist();

    return (
        <ul className="flex lg:gap-7">
            <MenuIconicItem className="flex max-lg:hidden">
                <MenuIconicItemLink href={storesUrl} type="stores">
                    <MarkerIcon className="size-6" />
                    {t('Stores')}
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem>
                <MenuIconicItemLink href={productComparisonUrl} title={t('Comparison')} type="comparison">
                    <div className="relative">
                        <CompareIcon className="size-6" />
                        {/* {!!comparison?.products.length && (
                            <span className={menuCountTwClass}>{comparison.products.length}</span>
                        )} */}
                    </div>
                    <span className="max-lg:hidden">{t('Comparison')}</span>
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem>
                <MenuIconicItemLink href={wishlistUrl} title={t('Wishlist')} type="wishlist">
                    <div className="relative">
                        <HeartIcon className="size-6" />
                        {/* {!!wishlist?.products.length && (
                            <span className={menuCountTwClass}>{wishlist.products.length}</span>
                        )} */}
                    </div>
                    <span className="max-lg:hidden">{t('Favorites')}</span>
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem>
                <MenuIconicItemUserAuthentication />
            </MenuIconicItem>
        </ul>
    );
}
