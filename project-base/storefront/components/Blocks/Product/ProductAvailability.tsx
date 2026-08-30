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
    isPersonalPickupOnly?: boolean;
    /**
     * Classes of the second line (the shipping/pickup readiness), e.g. to drop its indentation under the name
     * when the availability is centered
     */
    detailsClassName?: string;
};

export const ProductAvailability: FC<ProductAvailabilityProps> = ({
    availability,
    availableStoresCount,
    className,
    displayMode = 'default',
    isInquiryType,
    isPersonalPickupOnly = false,
    detailsClassName,
}) => {
    const { t } = useTranslation();
    const inStockAvailabilityDetails = getInStockAvailabilityDetails(
        availability.status,
        availableStoresCount,
        isPersonalPickupOnly,
        t,
    );
    const availabilityText = isInquiryType
        ? null
        : `${availability.name}${inStockAvailabilityDetails ? `, ${inStockAvailabilityDetails}` : ''}`;

    const isCompactDisplay = displayMode === 'compact';
    const isDetailDisplay = displayMode === 'detail';

    return (
        <span
            data-tid={TIDs.product_availability}
            className={twMergeCustom(
                'flex text-left text-sm',
                isCompactDisplay ? 'items-center gap-1 text-xs' : 'flex-col gap-0.5',
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
                        <span className="flex min-w-0 items-start gap-1">
                            <ProductAvailabilityIcon
                                className="mt-0.5 size-4 shrink-0 [&_path]:stroke-2"
                                status={availability.status}
                            />

                            <span className="font-secondary font-semibold">{availability.name}</span>
                        </span>

                        {inStockAvailabilityDetails && (
                            <span
                                className={twMergeCustom(
                                    // indented under the name, past the icon
                                    'pl-5 font-secondary text-text-less',
                                    isDetailDisplay ? 'underline hover:text-link-default' : 'text-xs',
                                    detailsClassName,
                                )}
                            >
                                {inStockAvailabilityDetails}
                            </span>
                        )}
                    </>
                ))}
        </span>
    );
};
