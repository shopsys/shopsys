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
                <div className="vl:flex absolute -top-10 right-0 hidden items-center justify-center gap-2">
                    <SliderButtonPlaceholder type="prev" />
                    <SliderButtonPlaceholder type="next" />
                </div>
            )}

            <ul
                className={twJoin(
                    'hide-scrollbar grid snap-x snap-mandatory grid-flow-col overflow-x-auto overscroll-x-contain',
                    'vl:auto-cols-[25%] auto-cols-[225px] sm:auto-cols-[60%] md:auto-cols-[45%] lg:auto-cols-[30%] xl:auto-cols-[20%]',
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
    <button className="text-text-default hover:text-text-accent disabled:text-text-disabled cursor-pointer rounded-sm border-none p-1 outline-hidden transition disabled:cursor-auto">
        <ArrowSecondaryIcon className={twJoin('w-5', type === 'prev' ? 'rotate-90' : '-rotate-90')} />
    </button>
);
