import { ProductListItem } from './ProductsList/ProductListItem';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
// import { TIDs } from 'cypress/tids';
import { usePromotedProductsQuery } from 'graphql/requests/products/queries/PromotedProductsQuery.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';

// import dynamic from 'next/dynamic';

// const SkeletonModulePromotedProducts = dynamic(
//     () =>
//         import('components/Blocks/Skeleton/SkeletonModulePromotedProducts').then(
//             (component) => component.SkeletonModulePromotedProducts,
//         ),
//     { ssr: false },
// );

const ProductsSliderPlaceholder: FC = () => {
    const { t } = useTranslation();
    const [{ data: promotedProductsData }] = usePromotedProductsQuery();

    if (!promotedProductsData?.promotedProducts) {
        return null;
    }

    return (
        <div className="relative">
            <div className="absolute -top-11 right-0 hidden items-center justify-center vl:flex ">
                <button
                    className="ml-1 h-8 w-8 cursor-pointer rounded border-none bg-greyDark pt-1 text-creamWhite outline-none transition hover:bg-greyDarker disabled:bg-greyLighter"
                    disabled={false}
                    title={t('Previous products')}
                >
                    <ArrowIcon className="-translate-y-[2px] rotate-90" />
                </button>
                <button
                    className="ml-1 h-8 w-8 cursor-pointer rounded border-none bg-greyDark pt-1 text-creamWhite outline-none transition hover:bg-greyDarker disabled:bg-greyLighter"
                    disabled={promotedProductsData.promotedProducts.length <= 4}
                    title={t('Next products')}
                >
                    <ArrowIcon className="-translate-y-[2px] -rotate-90" />
                </button>
            </div>

            <ul className="grid snap-x snap-mandatory auto-cols-[80%] grid-flow-col overflow-x-auto overscroll-x-contain [-ms-overflow-style:'none'] [scrollbar-width:'none'] md:auto-cols-[45%] lg:auto-cols-[30%] vl:auto-cols-[25%] [&::-webkit-scrollbar]:hidden">
                {promotedProductsData.promotedProducts.map((product, index) =>
                    index < 4 ? (
                        <ProductListItem
                            key={product.uuid}
                            gtmMessageOrigin={GtmMessageOriginType.other}
                            gtmProductListName={GtmProductListNameType.homepage_promo_products}
                            listIndex={index}
                            product={product}
                        />
                    ) : (
                        <div key={product.uuid} className="hidden">
                            <ExtendedNextLink href={product.slug} />
                        </div>
                    ),
                )}
            </ul>
        </div>
    );
};

// const ProductsSlider = dynamic(() => import('./ProductsSlider').then((component) => component.ProductsSlider), {
//     ssr: false,
//     loading: () => <ProductsSliderPlaceholder />,
// });

export const PromotedProducts: FC = () => {
    // const [{ data: promotedProductsData }] = usePromotedProductsQuery();

    // // if (fetching) {
    // //     return <SkeletonModulePromotedProducts />;
    // // }

    // if (!promotedProductsData?.promotedProducts) {
    //     return null;
    // }

    return <ProductsSliderPlaceholder />;
};
