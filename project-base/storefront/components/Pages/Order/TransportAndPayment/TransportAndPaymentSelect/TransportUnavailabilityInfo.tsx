import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { Image } from 'components/Basic/Image/Image';
import { TypeTransportWithAvailablePaymentsFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsFragment.generated';
import { useState } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';

const INITIAL_VISIBLE_PRODUCTS_COUNT = 2;

type TransportUnavailabilityInfoProps = {
    heading: string;
    products: TypeTransportWithAvailablePaymentsFragment['productsBlockingSelectionInCart'][number]['products'];
};

export const TransportUnavailabilityInfo: FC<TransportUnavailabilityInfoProps> = ({ heading, products }) => {
    const { t } = useTranslation();
    const [areAllProductsShown, setAreAllProductsShown] = useState(false);

    const visibleProducts = areAllProductsShown ? products : products.slice(0, INITIAL_VISIBLE_PRODUCTS_COUNT);
    const hiddenProductsCount = products.length - INITIAL_VISIBLE_PRODUCTS_COUNT;

    return (
        <div className="flex w-full flex-col gap-2 p-5 pt-0 text-sm text-text-less">
            <div className="flex items-center gap-2">
                <InfoIcon className="size-4 shrink-0" />

                <span>{heading}</span>
            </div>

            <ul className="flex flex-col gap-1 pl-6">
                {visibleProducts.map((product) => (
                    <li key={product.uuid} className="flex items-center gap-2.5">
                        <div className="flex size-7 items-center justify-center rounded-full">
                            <Image
                                alt={product.fullName}
                                className="size-auto max-h-7 max-w-7 mix-blend-multiply"
                                height={24}
                                src={product.mainImage?.url}
                                width={48}
                            />
                        </div>

                        <span>{product.fullName}</span>
                    </li>
                ))}
            </ul>

            {hiddenProductsCount > 0 && (
                <button
                    className="ml-6 w-fit cursor-pointer rounded-sm text-text-less hover:underline"
                    type="button"
                    onClick={() => setAreAllProductsShown((previousValue) => !previousValue)}
                >
                    {areAllProductsShown
                        ? t('Show less')
                        : t('+ show {{ count }} more', { count: hiddenProductsCount })}
                </button>
            )}
        </div>
    );
};
