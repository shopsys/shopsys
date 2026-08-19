import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { twJoin } from 'tailwind-merge';
import { ProductPrice } from './ProductPrice';
import { ProductItemProps } from './ProductsList/ProductListItem';
import { ProductListItemPlaceholder } from './ProductsList/ProductListItemPlaceholder';
import { getProductsSliderTwClass, type ProductsSliderProps, VISIBLE_SLIDER_ITEMS } from './ProductsSlider';

type ProductsSliderPlaceholderProps = {
    size?: ProductItemProps['size'];
    visibleItemsConfig?: ProductItemProps['visibleItemsConfig'];
} & Pick<ProductsSliderProps, 'products' | 'variant' | 'visibleSliderItems'>;

export const ProductsSliderPlaceholder: FC<ProductsSliderPlaceholderProps> = ({
    products,
    visibleItemsConfig,
    size,
    variant = 'default',
    visibleSliderItems = VISIBLE_SLIDER_ITEMS,
}) => {
    return (
        <div className="relative">
            {products.length > visibleSliderItems && (
                <div className="absolute -top-10 right-0 vl:flex hidden items-center justify-center gap-2">
                    <SliderButtonPlaceholder type="prev" />
                    <SliderButtonPlaceholder type="next" />
                </div>
            )}

            <ul
                className={twJoin(
                    'hide-scrollbar grid snap-x snap-mandatory grid-flow-col overflow-x-auto overscroll-x-contain',
                    getProductsSliderTwClass(variant),
                )}
            >
                {products.map((product, index) =>
                    index < visibleSliderItems ? (
                        <ProductListItemPlaceholder
                            key={product.uuid}
                            className="mx-1.5 first:ml-0 last:mr-0"
                            product={product}
                            size={size}
                            visibleItemsConfig={visibleItemsConfig}
                        />
                    ) : (
                        <li key={product.uuid} className="mx-1.5">
                            <ExtendedNextLink href={product.slug}>
                                {product.fullName}
                                <ProductPrice productPrice={product.price} />
                            </ExtendedNextLink>
                        </li>
                    ),
                )}
            </ul>
        </div>
    );
};

type SliderButtonPlaceholderProps = { type: 'prev' | 'next' };

const SliderButtonPlaceholder: FC<SliderButtonPlaceholderProps> = ({ type }) => (
    <span aria-hidden="true" className="inline-flex size-8 items-center justify-center text-icon-less">
        <ArrowSecondaryIcon className={twJoin('size-6', type === 'prev' ? 'rotate-90' : '-rotate-90')} />
    </span>
);
