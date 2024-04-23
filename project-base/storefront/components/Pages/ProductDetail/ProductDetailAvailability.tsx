import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { RefObject } from 'react';
import { twJoin } from 'tailwind-merge';

type ProductDetailAvailabilityProps = {
    product: TypeProductDetailFragment;
    scrollTarget: RefObject<HTMLUListElement>;
};

export const ProductDetailAvailability: FC<ProductDetailAvailabilityProps> = ({ product, scrollTarget }) => {
    const { t } = useTranslation();

    const scrollOnClickHandler = () => {
        if (scrollTarget.current !== null) {
            scrollTarget.current.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    return (
        <div className="rounded bg-whiteSnow px-3 py-4">
            <div
                className={twJoin(
                    'flex cursor-pointer items-center font-bold no-underline hover:no-underline',
                    product.availability.status === TypeAvailabilityStatusEnum.InStock &&
                        'text-secondary hover:text-secondary',
                    product.availability.status === TypeAvailabilityStatusEnum.OutOfStock && 'text-red hover:text-red',
                )}
                onClick={scrollOnClickHandler}
            >
                {product.availability.name}
                <ArrowIcon className="ml-1 text-dark" />
            </div>

            {!!product.availableStoresCount && (
                <span className="mr-1 text-sm">
                    {t('This item is available immediately in {{ count }} stores', {
                        count: product.availableStoresCount,
                    })}
                </span>
            )}
        </div>
    );
};
