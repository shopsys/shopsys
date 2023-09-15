import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowRightIcon } from 'components/Basic/Icon/IconsSvg';
import { AvailabilityStatusEnumApi, StoreAvailabilityFragmentApi } from 'graphql/generated';
import useTranslation from 'next-translate/useTranslation';
import { forwardRef } from 'react';
import { twJoin } from 'tailwind-merge';

type ProductDetailAvailabilityListProps = {
    storeAvailabilities: StoreAvailabilityFragmentApi[];
};

export const ProductDetailAvailabilityList = forwardRef<HTMLUListElement, ProductDetailAvailabilityListProps>(
    ({ storeAvailabilities }, ref) => {
        const { t } = useTranslation();

        return (
            <div className="vl:max-w-xl">
                <div className="text-xl font-bold">{t('Availability in stores')}</div>

                <ul ref={ref}>
                    {storeAvailabilities.map(
                        (storeAvailability, index) =>
                            storeAvailability.store && (
                                <li className="flex w-full items-center border-b border-greyLighter py-4" key={index}>
                                    <strong className="mr-2 w-36">{storeAvailability.store.storeName}</strong>

                                    <span
                                        className={twJoin(
                                            'flex-1 pr-3 text-sm',
                                            storeAvailability.availabilityStatus ===
                                                AvailabilityStatusEnumApi.InStockApi && 'text-inStock',
                                            storeAvailability.availabilityStatus ===
                                                AvailabilityStatusEnumApi.OutOfStockApi && 'text-red',
                                        )}
                                    >
                                        {storeAvailability.availabilityInformation}
                                    </span>

                                    <ExtendedNextLink
                                        href={storeAvailability.store.slug}
                                        type="store"
                                        className="ml-auto flex items-center text-dark no-underline hover:text-dark hover:no-underline"
                                    >
                                        {t('Store detail')}
                                        <ArrowRightIcon className="ml-1" />
                                    </ExtendedNextLink>
                                </li>
                            ),
                    )}
                </ul>
            </div>
        );
    },
);

ProductDetailAvailabilityList.displayName = 'ProductDetailAvailabilityList';
