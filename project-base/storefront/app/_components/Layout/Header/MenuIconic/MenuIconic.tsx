import { MenuIconicItem, MenuIconicItemLink } from './MenuIconicElements';
import MenuIconicItemUserAuthentication from './MenuIconicItemUserAuthentication';
import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import { HeartIcon } from 'components/Basic/Icon/HeartIcon';
import { MarkerIcon } from 'components/Basic/Icon/MarkerIcon';
import { headers } from 'next/headers';
import { getDomainConfig } from 'utils/domain/domainConfig';
import { getServerT } from 'utils/getServerTranslation';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export async function MenuIconic() {
    const t = await getServerT();
    const { url } = getDomainConfig(headers().get('host')!);
    const [storesUrl, productComparisonUrl, wishlistUrl] = getInternationalizedStaticUrls(
        ['/stores', '/product-comparison', '/wishlist'],
        url,
    );
    // TODO: wishlist and comparison hooks
    // const { comparison } = useComparison();
    // const { wishlist } = useWishlist();

    const menuCountTwClass =
        'absolute -right-2 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-activeIconFull px-0.5 font-secondary text-[10px] font-bold leading-normal text-textInverted lg:-right-2 lg:-top-[6.5px]';

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
