import { Icon } from 'components/Basic/Icon/Icon';
import { AvailabilityStatusEnumApi, ProductDetailFragmentApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { RefObject, useEffect } from 'react';
import * as smoothscroll from 'smoothscroll-polyfill';
import { twJoin } from 'tailwind-merge';

type ProductDetailAvailabilityProps = {
    product: ProductDetailFragmentApi;
    scrollTarget: RefObject<HTMLUListElement>;
};

const TEST_IDENTIFIER = 'pages-productdetail-productdetailavailability';

export const ProductDetailAvailability: FC<ProductDetailAvailabilityProps> = ({ product, scrollTarget }) => {
    const t = useTypedTranslationFunction();

    const scrollOnClickHandler = () => {
        if (scrollTarget.current !== null) {
            scrollTarget.current.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    useEffect(() => {
        smoothscroll.polyfill();
    }, []);

    return (
        <div className="rounded-md bg-blueLight px-3 py-4" data-testid={TEST_IDENTIFIER}>
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
                <Icon iconType="icon" icon="Arrow" className="text-dark" />
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
