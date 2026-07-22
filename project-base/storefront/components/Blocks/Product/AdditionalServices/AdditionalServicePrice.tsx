import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';
import { twMergeCustom } from 'utils/twMerge';

type AdditionalServiceAddOnPriceProps = {
    isHighlighted?: boolean;
    priceWithVat: string;
    quantity?: number;
    showAddSign?: boolean;
    showUnit: boolean;
    unitName: string;
};

export const AdditionalServiceAddOnPrice: FC<AdditionalServiceAddOnPriceProps> = ({
    isHighlighted,
    priceWithVat,
    quantity,
    showAddSign = true,
    showUnit,
    unitName,
}) => {
    const formatPrice = useFormatPrice();

    if (!isPriceVisible(priceWithVat)) {
        return null;
    }

    const displayedPrice = quantity === undefined ? priceWithVat : mapPriceForCalculations(priceWithVat) * quantity;

    return (
        <span className="inline-flex items-baseline gap-1 whitespace-nowrap font-secondary text-sm">
            <span className={twMergeCustom('font-semibold text-text-default', isHighlighted && 'text-price-default')}>
                {showAddSign && '+ '}
                {formatPrice(displayedPrice)}
            </span>
            {showUnit && <span className="text-sm text-text-less">/ {unitName}</span>}
        </span>
    );
};

type AdditionalServiceCartPriceProps = {
    priceWithVat: string;
    quantity: number;
    unitName: string;
};

export const AdditionalServiceCartPrice: FC<AdditionalServiceCartPriceProps> = ({
    priceWithVat,
    quantity,
    unitName,
}) => {
    const formatPrice = useFormatPrice();

    if (!isPriceVisible(priceWithVat)) {
        return null;
    }

    const displayedPrice = mapPriceForCalculations(priceWithVat) * quantity;

    return (
        <>
            <span className="flex vl:hidden w-full items-baseline justify-between whitespace-nowrap font-secondary md:w-auto md:flex-col md:items-end">
                <span className="inline-flex items-baseline gap-1 text-sm">
                    <span className="font-semibold text-text-default">{formatPrice(priceWithVat)}</span>
                    <span className="text-text-less">/ {unitName}</span>
                </span>
                <span className="ml-auto font-semibold text-price-default text-sm md:ml-0">
                    {formatPrice(displayedPrice)}
                </span>
            </span>

            <span className="vl:col-start-3 vl:block hidden whitespace-nowrap font-secondary">
                <span className="font-semibold">{formatPrice(priceWithVat)}</span>
                <span className="text-sm text-text-less">&nbsp;/&nbsp;{unitName}</span>
            </span>

            <span className="vl:col-start-4 vl:flex hidden items-center justify-end">
                <span className="whitespace-nowrap text-right font-bold font-secondary text-price-default">
                    {formatPrice(displayedPrice)}
                </span>
            </span>
        </>
    );
};
