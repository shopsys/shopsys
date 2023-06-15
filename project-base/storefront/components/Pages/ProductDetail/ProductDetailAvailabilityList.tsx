import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { AvailabilityStatusEnumApi, StoreAvailabilityFragmentApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import NextLink from 'next/link';
import { forwardRef } from 'react';
import { twJoin } from 'tailwind-merge';

type ProductDetailAvailabilityListProps = {
    storeAvailabilities: StoreAvailabilityFragmentApi[];
};

const TEST_IDENTIFIER = 'pages-productdetail-availabilitylist-';

export const ProductDetailAvailabilityList = forwardRef<HTMLUListElement, ProductDetailAvailabilityListProps>(
    ({ storeAvailabilities }, ref) => {
        const t = useTypedTranslationFunction();

        return (
            <div className="block w-full vl:max-w-xl">
                <Heading type="h3">{t('Availability in stores')}</Heading>
                <ul ref={ref}>
                    {storeAvailabilities.map(
                        (storeAvailability, index) =>
                            storeAvailability.store !== null && (
                                <li
                                    className="flex w-full items-center border-b border-greyLighter py-4"
                                    key={index}
                                    data-testid={TEST_IDENTIFIER + index}
                                >
                                    <strong className="mr-2 w-36" data-testid={TEST_IDENTIFIER + index + '-store'}>
                                        {storeAvailability.store.storeName}
                                    </strong>
                                    <span
                                        className={twJoin(
                                            'flex-1 pr-3 text-sm',
                                            storeAvailability.availabilityStatus ===
                                                AvailabilityStatusEnumApi.InStockApi && 'text-inStock',
                                            storeAvailability.availabilityStatus ===
                                                AvailabilityStatusEnumApi.OutOfStockApi && 'text-red',
                                        )}
                                        data-testid={TEST_IDENTIFIER + index + '-availability'}
                                    >
                                        {storeAvailability.availabilityInformation}
                                    </span>
                                    <NextLink href={storeAvailability.store.slug} passHref>
                                        <a
                                            className="ml-auto flex items-center text-dark no-underline hover:text-dark hover:no-underline"
                                            data-testid={TEST_IDENTIFIER + index + '-detail'}
                                        >
                                            {t('Store detail')}
                                            <Icon iconType="icon" icon="ArrowRight" />
                                        </a>
                                    </NextLink>
                                </li>
                            ),
                    )}
                </ul>
            </div>
        );
    },
);

ProductDetailAvailabilityList.displayName = 'ProductDetailAvailabilityList';
