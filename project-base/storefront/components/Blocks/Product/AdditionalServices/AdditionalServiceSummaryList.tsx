import { Image } from 'components/Basic/Image/Image';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

export type AdditionalServiceSummaryLine = {
    uuid: string;
    name: string;
    code?: string | null;
    deliveryDaysExtension?: number | null;
    imageUrl?: string | null;
    quantityLabel?: string;
    unitPriceLabel?: string;
    priceLabel: string | null;
};

type AdditionalServiceSummaryListProps = {
    services: AdditionalServiceSummaryLine[];
    className?: string;
    showHeading?: boolean;
    isPriceHighlighted?: boolean;
};

export const AdditionalServiceSummaryList: FC<AdditionalServiceSummaryListProps> = ({
    services,
    className,
    showHeading,
    isPriceHighlighted = true,
}) => {
    const { t } = useTranslation();

    if (services.length === 0) {
        return null;
    }

    return (
        <div className={twMergeCustom('flex flex-col gap-2', className)}>
            {showHeading && <span className="font-semibold text-sm">{t('Additional services')}</span>}

            <ul className="flex flex-col gap-2">
                {services.map((service) => (
                    <li
                        key={service.uuid}
                        className="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-2 text-sm text-text-less"
                    >
                        <span className="flex min-w-0 items-center gap-2">
                            {service.imageUrl && (
                                <Image
                                    alt=""
                                    className="size-6 shrink-0 object-contain mix-blend-multiply"
                                    height={24}
                                    src={service.imageUrl}
                                    width={24}
                                />
                            )}

                            <span className="flex min-w-0 flex-col">
                                <span className="wrap-break-words font-semibold text-text-default">{service.name}</span>

                                {service.deliveryDaysExtension !== null &&
                                    service.deliveryDaysExtension !== undefined &&
                                    service.deliveryDaysExtension > 0 && (
                                        <span className="font-normal text-text-less text-xs">
                                            {t('+{{ count }} working days', {
                                                count: service.deliveryDaysExtension,
                                            })}
                                        </span>
                                    )}

                                {(service.code || service.quantityLabel) && (
                                    <span className="font-normal text-xs">
                                        {service.code && `${t('Code')}: ${service.code}`}
                                        {service.code && service.quantityLabel && ' · '}
                                        {service.quantityLabel}
                                    </span>
                                )}
                            </span>
                        </span>

                        {(service.unitPriceLabel || service.priceLabel !== null) && (
                            <span className="flex flex-col items-end whitespace-nowrap">
                                {service.priceLabel !== null && (
                                    <span
                                        className={twMergeCustom(
                                            'font-semibold',
                                            isPriceHighlighted ? 'text-price-default' : 'text-text-default',
                                        )}
                                    >
                                        {service.priceLabel}
                                    </span>
                                )}
                                {service.unitPriceLabel && (
                                    <span className="font-normal text-text-default text-xs">
                                        {service.unitPriceLabel}
                                    </span>
                                )}
                            </span>
                        )}
                    </li>
                ))}
            </ul>
        </div>
    );
};
