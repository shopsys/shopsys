import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { TypeSimpleStoreAvailabilityFragment } from 'graphql/requests/storeAvailabilities/fragments/SimpleStoreAvailabilityFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ProductDetailAvailabilityListProps = {
    storeAvailabilities: TypeSimpleStoreAvailabilityFragment[];
};

export const ProductDetailAvailabilityList: FC<ProductDetailAvailabilityListProps> = ({ storeAvailabilities }) => {
    const { t } = useTranslation();

    return (
        <div className="vl:max-w-xl">
            <ul>
                {storeAvailabilities.map(
                    (storeAvailability) =>
                        storeAvailability.store && (
                            <li
                                key={storeAvailability.store.slug}
                                className="flex w-full items-center justify-between gap-4 border-border-default border-b py-4"
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
                                        ns: 'accessibility',
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
