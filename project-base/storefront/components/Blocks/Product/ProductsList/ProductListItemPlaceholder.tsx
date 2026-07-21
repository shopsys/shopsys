import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { ProductActionSkeleton } from 'components/Blocks/Skeleton/ProductActionSkeleton';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { FunctionComponentProps } from 'types/globals';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { PREDEFINED_VISIBLE_ITEMS_CONFIGS, ProductItemProps } from './ProductListItem';
import { ProductListItemImage } from './ProductListItemImage';

type ProductListItemPlaceholderProps = {
    product: TypeListedProductFragment;
    size?: ProductItemProps['size'];
    visibleItemsConfig?: ProductItemProps['visibleItemsConfig'];
    productListViewMode?: ProductItemProps['productListViewMode'];
} & FunctionComponentProps;

export const ProductListItemPlaceholder: FC<ProductListItemPlaceholderProps> = ({
    product,
    className,
    visibleItemsConfig = PREDEFINED_VISIBLE_ITEMS_CONFIGS.largeItem,
    productListViewMode = 'grid',
    size = 'large',
}) => {
    const { t } = useTranslation();

    if (productListViewMode === 'list') {
        return (
            <li
                className={twMergeCustom(
                    'group relative grid select-none gap-4 rounded-xl border border-background-more bg-background-more p-3 text-left transition',
                    'hover:border-border-less hover:bg-background',
                    'sm:p-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-center',
                    className,
                )}
            >
                <ExtendedNextLink
                    className="grid min-w-0 grid-cols-[80px_minmax(0,1fr)] gap-3 rounded-xl text-text-default no-underline hover:text-link-default hover:no-underline sm:gap-4 xl:w-fit xl:grid-cols-[88px_minmax(0,280px)_minmax(0,280px)] xl:items-center"
                    draggable={false}
                    href={product.slug}
                    type={product.isMainVariant ? 'productMainVariant' : 'product'}
                >
                    <div className="flex justify-center">
                        <ProductListItemImage
                            product={product}
                            size="extraSmall"
                            visibleItemsConfig={{ ...visibleItemsConfig, discount: false, flags: false }}
                        />
                    </div>

                    <div className="flex min-w-0 flex-col justify-center gap-1 xl:max-w-70">
                        <ProductFlags
                            flags={product.flags}
                            percentageDiscount={product.price.percentageDiscount}
                            variant="list"
                            visibleItemsConfig={visibleItemsConfig}
                        />

                        <div className="wrap-break-word overflow-hidden font-secondary font-semibold text-sm group-hover:text-link-default group-hover:underline xl:max-w-70">
                            {product.fullName}
                        </div>

                        <div className="text-text-less text-xs">
                            {t('Code')}: {product.catalogNumber}
                        </div>
                    </div>

                    {visibleItemsConfig.storeAvailability && !product.isSellingDenied && (
                        <ProductAvailability
                            availability={product.availability}
                            availableStoresCount={product.availableStoresCount}
                            className="col-span-2 min-h-0 text-sm leading-5 xl:col-span-1 xl:max-w-70 xl:justify-self-start"
                            isInquiryType={product.isInquiryType}
                        />
                    )}
                </ExtendedNextLink>

                <div className="flex min-w-0 flex-col items-end justify-center gap-2">
                    {visibleItemsConfig.price && !(product.isMainVariant && product.isSellingDenied) && (
                        <ProductPrice
                            className="justify-end text-right"
                            isPriceFromVisible={visibleItemsConfig.priceFromWord}
                            productPrice={product.price}
                            textPriceSize="lg"
                        />
                    )}

                    {visibleItemsConfig.addToCart && (
                        <ProductActionSkeleton
                            className="h-9 w-40 shrink-0 sm:w-44"
                            isWithAddToCart
                            isWithProductListButtons={false}
                        />
                    )}
                </div>
            </li>
        );
    }

    return (
        <li
            className={twMergeCustom(
                'group relative flex select-none flex-col gap-2.5 rounded-xl border border-background-more bg-background-more px-2.5 py-5 text-left transition sm:px-5',
                size === 'small' && 'p-5',
                'hover:border-border-less hover:bg-background',
                className,
            )}
        >
            <ExtendedNextLink
                className="flex select-none flex-col gap-2.5 text-text-default no-underline hover:text-link-default hover:no-underline"
                draggable={false}
                href={product.slug}
                type={product.isMainVariant ? 'productMainVariant' : 'product'}
            >
                <ProductListItemImage product={product} size={size} visibleItemsConfig={visibleItemsConfig} />

                <div className="line-clamp-3 min-h-15 font-secondary font-semibold text-sm group-hover:text-link-default group-hover:underline">
                    {product.fullName}
                </div>

                {visibleItemsConfig.price && !(product.isMainVariant && product.isSellingDenied) && (
                    <ProductPrice
                        className="min-h-6 sm:min-h-7"
                        isPriceFromVisible={visibleItemsConfig.priceFromWord}
                        productPrice={product.price}
                    />
                )}

                {visibleItemsConfig.storeAvailability && (
                    <ProductAvailability
                        availability={product.availability}
                        availableStoresCount={product.availableStoresCount}
                        className="min-h-10 xs:min-h-15 sm:min-h-10"
                        isInquiryType={product.isInquiryType}
                    />
                )}
            </ExtendedNextLink>

            {visibleItemsConfig.addToCart && <ProductActionSkeleton isWithAddToCart isWithProductListButtons={false} />}
        </li>
    );
};
