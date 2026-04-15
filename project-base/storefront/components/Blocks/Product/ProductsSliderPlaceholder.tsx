import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { twJoin } from 'tailwind-merge';
import { ProductPrice } from './ProductPrice';
import { ProductItemProps } from './ProductsList/ProductListItem';
import { ProductListItemPlaceholder } from './ProductsList/ProductListItemPlaceholder';
import { ProductsSliderProps, VISIBLE_SLIDER_ITEMS } from './ProductsSlider';

type ProductsSliderPlaceholderProps = {
    size?: ProductItemProps['size'];
    visibleItemsConfig?: ProductItemProps['visibleItemsConfig'];
} & Pick<ProductsSliderProps, 'products'>;

export const ProductsSliderPlaceholder: FC<ProductsSliderPlaceholderProps> = ({
    products,
    visibleItemsConfig,
    size,
}) => {
    return (
        <div className="relative">
            {products.length > VISIBLE_SLIDER_ITEMS && (
                <div className="absolute -top-10 right-0 vl:flex hidden items-center justify-center gap-2">
                    <SliderButtonPlaceholder type="prev" />
                    <SliderButtonPlaceholder type="next" />
                </div>
            )}

            <ul
                className={twJoin(
                    'hide-scrollbar grid snap-x snap-mandatory grid-flow-col overflow-x-auto overscroll-x-contain',
                    'auto-cols-[225px] vl:auto-cols-[25%] sm:auto-cols-[60%] md:auto-cols-[45%] lg:auto-cols-[30%] xl:auto-cols-[20%]',
                )}
            >
                {products.map((product, index) =>
                    index < VISIBLE_SLIDER_ITEMS ? (
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
    <button className="cursor-pointer rounded-sm border-none p-1 text-text-default outline-hidden transition hover:text-text-accent disabled:cursor-auto disabled:text-text-disabled">
        <ArrowSecondaryIcon className={twJoin('w-5', type === 'prev' ? 'rotate-90' : '-rotate-90')} />
    </button>
);
