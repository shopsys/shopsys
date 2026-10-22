import { TIDs } from 'cypress/tids';
import { TypeAvailability } from 'graphql/types';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInStockAvailabilityDetails } from 'utils/product/getInStockAvailabilityDetails';
import { twMergeCustom } from 'utils/twMerge';
import { getAvailabilityTextColorClassName } from 'utils/ui/getAvailabilityTextColorClassName';
import { ProductAvailabilityIcon } from './ProductAvailabilityIcon';

type ProductAvailabilityProps = {
    id?: string;
    availability: TypeAvailability;
    availableStoresCount: number | null;
    displayMode?: 'compact' | 'default' | 'detail' | 'inline';
    isInquiryType: boolean;
    isPersonalPickupOnly?: boolean;
    /**
     * Classes of the shipping/pickup readiness details, e.g. to drop their indentation under the name
     * when the availability is centered
     */
    detailsClassName?: string;
};

export const ProductAvailability: FC<ProductAvailabilityProps> = ({
    id,
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
    const isInlineDisplay = displayMode === 'inline';

    return (
        <span
            id={id}
            data-tid={TIDs.product_availability}
            className={twMergeCustom(
                'flex text-left text-sm',
                isCompactDisplay ? 'items-center gap-1 text-xs' : 'flex-col gap-0.5',
                isInlineDisplay && 'flex-row flex-wrap items-center gap-x-2.5 overflow-hidden',
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
                        <span
                            className={twMergeCustom(
                                'flex min-w-0 items-start gap-1',
                                isInlineDisplay && 'items-center',
                            )}
                        >
                            <ProductAvailabilityIcon
                                className={twMergeCustom(
                                    'mt-0.5 size-4 shrink-0 [&_path]:stroke-2',
                                    isInlineDisplay && 'mt-0',
                                )}
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
                                    isInlineDisplay && 'relative pl-0',
                                    detailsClassName,
                                )}
                            >
                                {isInlineDisplay && (
                                    // Clip the separator outside the container when the details wrap to a new line.
                                    <span aria-hidden="true" className="absolute -left-1.5">
                                        ·
                                    </span>
                                )}
                                {inStockAvailabilityDetails}
                            </span>
                        )}
                    </>
                ))}
        </span>
    );
};
