import { Arrow } from 'components/Basic/Icon/IconsSvg';
import { AvailabilityStatusEnumApi, ProductDetailFragmentApi } from 'graphql/generated';
import useTranslation from 'next-translate/useTranslation';
import { RefObject } from 'react';
import { twJoin } from 'tailwind-merge';

type ProductDetailAvailabilityProps = {
    product: ProductDetailFragmentApi;
    scrollTarget: RefObject<HTMLUListElement>;
};

const TEST_IDENTIFIER = 'pages-productdetail-productdetailavailability';

export const ProductDetailAvailability: FC<ProductDetailAvailabilityProps> = ({ product, scrollTarget }) => {
    const { t } = useTranslation();

    const scrollOnClickHandler = () => {
        if (scrollTarget.current !== null) {
            scrollTarget.current.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    return (
        <div className="rounded bg-blueLight px-3 py-4" data-testid={TEST_IDENTIFIER}>
            <a
                className={twJoin(
                    'flex items-center font-bold no-underline hover:no-underline',
                    product.availability.status === AvailabilityStatusEnumApi.InStockApi &&
                        'text-inStock hover:text-inStock',
                    product.availability.status === AvailabilityStatusEnumApi.OutOfStockApi &&
                        'text-red hover:text-red',
                )}
                onClick={scrollOnClickHandler}
            >
                {product.availability.name}
                <Arrow className="text-dark" />
            </a>
            {product.availableStoresCount > 0 && (
                <span className="mr-1 text-sm" data-testid={TEST_IDENTIFIER + '-availability'}>
                    {t('This item is available immediately in {{ count }} stores', {
                        count: product.availableStoresCount,
                    })}
                </span>
            )}
            {product.exposedStoresCount > 0 && (
                <span className="mr-1 text-sm" data-testid={TEST_IDENTIFIER + '-exposed'}>
                    {t('You can check this item in {{ count }} stores', { count: product.exposedStoresCount })}
                </span>
            )}
        </div>
    );
};
