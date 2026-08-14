import { TIDs } from 'cypress/tids';
import { TypeAvailability } from 'graphql/types';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInStockAvailabilityDetails } from 'utils/product/getInStockAvailabilityDetails';
import { twMergeCustom } from 'utils/twMerge';
import { getAvailabilityTextColorClassName } from 'utils/ui/getAvailabilityTextColorClassName';
import { ProductAvailabilityIcon } from './ProductAvailabilityIcon';

type ProductAvailabilityProps = {
    availability: TypeAvailability;
    availableStoresCount: number | null;
    displayMode?: 'compact' | 'default' | 'detail';
    isInquiryType: boolean;
};

export const ProductAvailability: FC<ProductAvailabilityProps> = ({
    availability,
    availableStoresCount,
    className,
    displayMode = 'default',
    isInquiryType,
}) => {
    const { t } = useTranslation();
    const inStockAvailabilityDetails = getInStockAvailabilityDetails(availability.status, availableStoresCount, t);
    const availabilityText = isInquiryType
        ? null
        : `${availability.name}${inStockAvailabilityDetails ? `, ${inStockAvailabilityDetails}` : ''}`;

    const isCompactDisplay = displayMode === 'compact';
    const isDetailDisplay = displayMode === 'detail';

    return (
        <span
            data-tid={TIDs.product_availability}
            className={twMergeCustom(
                'flex items-start gap-1 text-left text-sm',
                isCompactDisplay && 'items-center text-xs',
                getAvailabilityTextColorClassName(availability.status),
                className,
            )}
        >
            {availabilityText &&
                (isCompactDisplay ? (
                    <>
                        <ProductAvailabilityIcon className="size-3 shrink-0" status={availability.status} />

                        <span className="font-semibold">{availabilityText}</span>
                    </>
                ) : (
                    <>
                        <ProductAvailabilityIcon
                            className="mt-0.5 size-4 shrink-0 [&_path]:stroke-2"
                            status={availability.status}
                        />

                        <span className="flex min-w-0 flex-col gap-0.5">
                            <span className="font-secondary font-semibold">{availability.name}</span>

                            {inStockAvailabilityDetails && (
                                <span
                                    className={twMergeCustom(
                                        'font-secondary text-text-less',
                                        isDetailDisplay ? 'underline hover:text-link-default' : 'text-xs',
                                    )}
                                >
                                    {inStockAvailabilityDetails}
                                </span>
                            )}
                        </span>
                    </>
                ))}
        </span>
    );
};
