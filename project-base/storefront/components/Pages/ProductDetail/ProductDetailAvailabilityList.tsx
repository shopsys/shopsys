import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { TypeStoreAvailabilityFragment } from 'graphql/requests/storeAvailabilities/fragments/StoreAvailabilityFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';

type ProductDetailAvailabilityListProps = {
    storeAvailabilities: TypeStoreAvailabilityFragment[];
};

export const ProductDetailAvailabilityList: FC<ProductDetailAvailabilityListProps> = ({ storeAvailabilities }) => {
    const { t } = useTranslation();

    return (
        <div className="vl:max-w-xl">
            <ul>
                {storeAvailabilities.map(
                    (storeAvailability, index) =>
                        storeAvailability.store && (
                            <li
                                key={index}
                                className="border-border-default flex w-full items-center justify-between gap-4 border-b py-4"
                            >
                                <strong className="w-36">{storeAvailability.store.storeName}</strong>

                                <span
                                    className={twJoin(
                                        'flex-1 pr-3 text-sm',
                                        storeAvailability.availabilityStatus === TypeAvailabilityStatusEnum.InStock &&
                                            'text-availability-in-stock',
                                        storeAvailability.availabilityStatus ===
                                            TypeAvailabilityStatusEnum.OutOfStock && 'text-availability-out-of-stock',
                                    )}
                                >
                                    {storeAvailability.availabilityInformation}
                                </span>

                                <ExtendedNextLink
                                    className="ml-auto flex items-center"
                                    href={storeAvailability.store.slug}
                                    type="store"
                                    aria-label={t('Store detail for {{storeName}}', {
                                        storeName: storeAvailability.store.storeName,
                                    })}
                                >
                                    {t('Store detail')}
                                </ExtendedNextLink>
                            </li>
                        ),
                )}
            </ul>
        </div>
    );
};
