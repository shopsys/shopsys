import { ProductPrice } from './ProductPrice';
import { ProductItemProps } from './ProductsList/ProductListItem';
import { ProductListItemPlaceholder } from './ProductsList/ProductListItemPlaceholder';
import { ProductsSliderProps, VISIBLE_SLIDER_ITEMS } from './ProductsSlider';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import useTranslation from 'next-translate/useTranslation';

type ProductsSliderPlaceholderProps = {
    size?: ProductItemProps['size'];
    visibleItemsConfig?: ProductItemProps['visibleItemsConfig'];
} & Pick<ProductsSliderProps, 'products'>;

export const ProductsSliderPlaceholder: FC<ProductsSliderPlaceholderProps> = ({
    products,
    visibleItemsConfig,
    size,
}) => {
    const { t } = useTranslation();

    return (
        <div className="relative">
            {products.length > VISIBLE_SLIDER_ITEMS && (
                <div className="absolute -top-11 right-0 hidden items-center justify-center vl:flex ">
                    <button
                        className="ml-1 h-8 w-8 cursor-pointer rounded border-none pt-1 outline-none transition"
                        title={t('Previous products')}
                    >
                        <ArrowSecondaryIcon className="w-5 -translate-y-[2px] rotate-90 text-text hover:text-textAccent disabled:text-textDisabled" />
                    </button>
                    <button
                        className="ml-1 h-8 w-8 cursor-pointer rounded border-none pt-1 outline-none transition"
                        title={t('Next products')}
                    >
                        <ArrowSecondaryIcon className="w-5 -translate-y-[2px] -rotate-90 text-text hover:text-textAccent disabled:text-textDisabled" />
                    </button>
                </div>
            )}

            <ul className="grid snap-x snap-mandatory auto-cols-[80%] grid-flow-col overflow-x-auto overscroll-x-contain [-ms-overflow-style:'none'] [scrollbar-width:'none'] md:auto-cols-[45%] lg:auto-cols-[30%] vl:auto-cols-[25%] [&::-webkit-scrollbar]:hidden">
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
