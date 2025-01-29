import { ProductPrice } from './ProductPrice';
import { ProductItemProps } from './ProductsList/ProductListItem';
import { ProductListItemPlaceholder } from './ProductsList/ProductListItemPlaceholder';
import { ProductsSliderProps, VISIBLE_SLIDER_ITEMS } from './ProductsSlider';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { twJoin } from 'tailwind-merge';

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
                <div className="absolute -top-10 right-0 hidden items-center justify-center gap-2 vl:flex">
                    <SliderButtonPlaceholder type="prev" />
                    <SliderButtonPlaceholder type="next" />
                </div>
            )}

            <ul
                className={twJoin(
                    "grid snap-x snap-mandatory grid-flow-col overflow-x-auto overscroll-x-contain [-ms-overflow-style:'none'] [scrollbar-width:'none'] [&::-webkit-scrollbar]:hidden",
                    'auto-cols-[225px] sm:auto-cols-[60%] md:auto-cols-[45%] lg:auto-cols-[30%] vl:auto-cols-[25%] xl:auto-cols-[20%]',
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
                        <ExtendedNextLink key={product.uuid} href={product.slug}>
                            {product.fullName}
                            <ProductPrice productPrice={product.price} />
                        </ExtendedNextLink>
                    ),
                )}
            </ul>
        </div>
    );
};

type SliderButtonPlaceholderProps = { type: 'prev' | 'next' };

const SliderButtonPlaceholder: FC<SliderButtonPlaceholderProps> = ({ type }) => (
    <button className="cursor-pointer rounded border-none p-1 text-text outline-none transition hover:text-textAccent disabled:cursor-auto disabled:text-textDisabled">
        <ArrowSecondaryIcon className={twJoin('w-5', type === 'prev' ? 'rotate-90' : '-rotate-90')} />
    </button>
);
