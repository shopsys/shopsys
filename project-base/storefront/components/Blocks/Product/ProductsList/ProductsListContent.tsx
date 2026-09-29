import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { TypeCompactProductFragment } from 'graphql/requests/products/fragments/CompactProductFragment.generated';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { Fragment, ReactNode, RefObject } from 'react';
import { SwipeableHandlers } from 'react-swipeable';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';
import { CompactProductItemProps, CompactProductListItem } from './CompactProductListItem';
import { ProductItemProps, ProductListItem, ProductListViewModeType } from './ProductListItem';

type ProductListPresentationProps<T extends TypeCompactProductFragment> = {
    products: T[];
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    ref?: RefObject<HTMLUListElement | null>;
    productRefs?: RefObject<HTMLLIElement | null>[];
    swipeHandlers?: SwipeableHandlers;
    className?: string;
    keyboardFocusableProductIndices?: number[];
    highlightFirstItemBadgeText?: string;
    children?: ReactNode;
};

type ItemPresentationProps<T> = {
    product: T;
    listIndex: number;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    ref?: RefObject<HTMLLIElement | null>;
    allowKeyboardFocus: boolean;
    highlightBadgeText?: string;
};

const ProductListPresentation = <T extends TypeCompactProductFragment>({
    products,
    gtmProductListName,
    gtmMessageOrigin = GtmMessageOriginType.other,
    ref,
    productRefs,
    swipeHandlers,
    className,
    keyboardFocusableProductIndices,
    highlightFirstItemBadgeText,
    children,
    renderItem,
}: ProductListPresentationProps<T> & { renderItem: (props: ItemPresentationProps<T>) => ReactNode }) => {
    const currentPage = useCurrentPageQuery();

    return (
        <ul className={className} ref={ref} {...swipeHandlers}>
            {products.map((product, index) => (
                <Fragment key={product.uuid}>
                    {renderItem({
                        product,
                        listIndex: (currentPage - 1) * DEFAULT_PAGE_SIZE + index,
                        gtmProductListName,
                        gtmMessageOrigin,
                        ref: productRefs?.[index],
                        allowKeyboardFocus:
                            !keyboardFocusableProductIndices || keyboardFocusableProductIndices.includes(index),
                        highlightBadgeText:
                            highlightFirstItemBadgeText && index === 0 && products.length > 1
                                ? highlightFirstItemBadgeText
                                : undefined,
                    })}
                </Fragment>
            ))}
            {children}
        </ul>
    );
};

type ProductsListProps = ProductListPresentationProps<TypeListedProductFragment & { imagesCount?: number }> & {
    productItemProps?: Partial<ProductItemProps>;
    productListViewMode?: ProductListViewModeType;
    isWithImageGallery?: boolean;
};

export const ProductsListContent: FC<ProductsListProps> = ({
    productItemProps,
    productListViewMode = 'grid',
    isWithImageGallery,
    ...props
}) => {
    const { toggleProductInComparison, isProductInComparison } = useComparison();
    const { toggleProductInWishlist, isProductInWishlist } = useWishlist();

    return (
        <ProductListPresentation
            {...props}
            renderItem={(item) => (
                <ProductListItem
                    {...item}
                    imageCount={item.product.imagesCount}
                    isWithImageGallery={isWithImageGallery}
                    productListViewMode={productListViewMode}
                    isProductInComparison={isProductInComparison(item.product.uuid)}
                    isProductInWishlist={isProductInWishlist(item.product.uuid)}
                    toggleProductInComparison={() =>
                        toggleProductInComparison(item.product, item.gtmProductListName, item.listIndex)
                    }
                    toggleProductInWishlist={() =>
                        toggleProductInWishlist(item.product, item.gtmProductListName, item.listIndex)
                    }
                    {...productItemProps}
                />
            )}
        />
    );
};

type CompactProductsListProps = ProductListPresentationProps<TypeCompactProductFragment> & {
    productItemProps?: Partial<CompactProductItemProps>;
};

export const CompactProductsListContent: FC<CompactProductsListProps> = ({ productItemProps, ...props }) => (
    <ProductListPresentation
        {...props}
        renderItem={(item) => <CompactProductListItem {...item} {...productItemProps} />}
    />
);
