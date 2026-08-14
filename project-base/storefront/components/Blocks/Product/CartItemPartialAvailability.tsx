import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { ProductAvailabilityIcon } from './ProductAvailabilityIcon';

type CartItemPartialAvailabilityProps = {
    stockQuantity: number;
    unitName: string;
    expectedRestockingDate: string | null;
};

export const CartItemPartialAvailability: FC<CartItemPartialAvailabilityProps> = ({
    className,
    stockQuantity,
    unitName,
    expectedRestockingDate,
}) => {
    const { t } = useTranslation();
    const { formatDate } = useFormatDate();

    return (
        <div className={twMergeCustom('flex flex-col gap-0.5 text-left', className)}>
            <span className="flex items-center gap-1 text-availability-in-stock">
                <ProductAvailabilityIcon className="size-3.5 shrink-0" status={TypeAvailabilityStatusEnum.InStock} />
                {t('In stock {{ quantity }} {{ unit }}', { quantity: stockQuantity, unit: unitName })}
            </span>
            {expectedRestockingDate ? (
                <span className="flex items-center gap-1 text-availability-expected-restock">
                    <ProductAvailabilityIcon
                        className="size-3.5 shrink-0"
                        status={TypeAvailabilityStatusEnum.ExpectedRestock}
                    />
                    {t('More expected on {{ date }}', { date: formatDate(expectedRestockingDate) })}
                </span>
            ) : (
                <span className="flex items-center gap-1 text-availability-out-of-stock">
                    <ProductAvailabilityIcon
                        className="size-3.5 shrink-0"
                        status={TypeAvailabilityStatusEnum.OutOfStock}
                    />
                    {t('More currently unavailable')}
                </span>
            )}
        </div>
    );
};
