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
            <div className="text-xl font-bold">{t('Availability in stores')}</div>

            <ul>
                {storeAvailabilities.map(
                    (storeAvailability, index) =>
                        storeAvailability.store && (
                            <li
                                key={index}
                                className="flex w-full items-center justify-between gap-4 border-b border-borderAccent py-4"
                            >
                                <strong className="w-36">{storeAvailability.store.storeName}</strong>

                                <span
                                    className={twJoin(
                                        'flex-1 pr-3 text-sm',
                                        storeAvailability.availabilityStatus === TypeAvailabilityStatusEnum.InStock &&
                                            'text-availabilityInStock',
                                        storeAvailability.availabilityStatus ===
                                            TypeAvailabilityStatusEnum.OutOfStock && 'text-availabilityOutOfStock',
                                    )}
                                >
                                    {storeAvailability.availabilityInformation}
                                </span>

                                <ExtendedNextLink
                                    className="ml-auto flex items-center"
                                    href={storeAvailability.store.slug}
                                    type="store"
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
