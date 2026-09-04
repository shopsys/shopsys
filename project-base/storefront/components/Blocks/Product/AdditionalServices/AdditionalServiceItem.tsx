import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import {
    AdditionalServiceImageLabel,
    AdditionalServiceLabel,
} from 'components/Blocks/Product/AdditionalServices/AdditionalServiceLabel';
import {
    AdditionalServiceAddOnPrice,
    AdditionalServiceCartPrice,
} from 'components/Blocks/Product/AdditionalServices/AdditionalServicePrice';
import { IconButton } from 'components/Forms/Button/IconButton';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { TIDs } from 'cypress/tids';
import { TypeAdditionalServiceFragment } from 'graphql/requests/additionalServices/fragments/AdditionalServiceFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type AdditionalServiceDescriptionButtonProps = {
    additionalService: TypeAdditionalServiceFragment;
    tidPrefix: string;
    onOpenDescription: (additionalService: TypeAdditionalServiceFragment) => void;
};

const AdditionalServiceDescriptionButton: FC<AdditionalServiceDescriptionButtonProps> = ({
    additionalService,
    tidPrefix,
    onOpenDescription,
}) => {
    const { t } = useTranslation();

    if (!additionalService.description) {
        return null;
    }

    const tooltipLabel = t('Show additional service description');

    return (
        <IconButton
            Icon={InfoIcon}
            ariaLabel={t('Show description of {{ serviceName }}', {
                ns: 'accessibility',
                serviceName: additionalService.name,
            })}
            className="align-middle"
            size="compact"
            tid={TIDs.additional_services_info_button_ + tidPrefix + additionalService.catnum}
            title={tooltipLabel}
            tooltipLabel={tooltipLabel}
            variant="ghost"
            onClick={() => onOpenDescription(additionalService)}
        />
    );
};

type AdditionalServiceItemProps = {
    additionalService: TypeAdditionalServiceFragment;
    checkboxInstanceId: string;
    isDisabled?: boolean;
    isInCartList?: boolean;
    isSelected: boolean;
    quantity?: number;
    showSelectedServiceTotalPrice?: boolean;
    tidPrefix: string;
    unitName: string;
    onOpenDescription: (additionalService: TypeAdditionalServiceFragment) => void;
    onToggleService: (additionalService: TypeAdditionalServiceFragment, isSelected: boolean) => void;
};

export const AdditionalServiceItem: FC<AdditionalServiceItemProps> = ({
    additionalService,
    checkboxInstanceId,
    isDisabled,
    isInCartList,
    isSelected,
    quantity,
    showSelectedServiceTotalPrice,
    tidPrefix,
    unitName,
    onOpenDescription,
    onToggleService,
}) => {
    const priceWithVat = additionalService.price.priceWithVat;
    const checkboxId = `additional-service-${checkboxInstanceId}-${additionalService.uuid}`;
    const isSelectedInCart = isInCartList === true && isSelected && quantity !== undefined;
    const isTotalPriceShown = showSelectedServiceTotalPrice === true && isSelected;
    const isUnitShown = isSelected ? !isTotalPriceShown : showSelectedServiceTotalPrice === true;
    const desktopPrice = !isSelectedInCart ? (
        <AdditionalServiceAddOnPrice
            isHighlighted={isTotalPriceShown}
            priceWithVat={priceWithVat}
            quantity={isTotalPriceShown ? quantity : undefined}
            showAddSign={!isTotalPriceShown}
            showUnit={isUnitShown}
            unitName={unitName}
        />
    ) : null;
    const mobilePrice = !isSelectedInCart ? (
        <AdditionalServiceAddOnPrice
            isHighlighted={isTotalPriceShown}
            priceWithVat={priceWithVat}
            quantity={isTotalPriceShown ? quantity : undefined}
            showAddSign={!isSelected}
            showUnit={isUnitShown}
            unitName={unitName}
        />
    ) : null;

    return (
        <li
            className={twMergeCustom(
                'relative grid min-w-0 grid-cols-[auto_auto_minmax(0,1fr)] items-center gap-x-2 md:grid-cols-[minmax(0,1fr)_auto] md:gap-3',
                isSelectedInCart && 'vl:contents',
                isInCartList && !isSelected && 'vl:col-start-1 vl:col-end-2',
            )}
        >
            <div
                className={twMergeCustom(
                    'contents md:col-span-1 md:col-start-1 md:flex md:min-w-0 md:gap-2 [&>div]:w-fit [&>div]:min-w-0 [&>div]:max-w-full',
                    isSelectedInCart &&
                        '[&>div]:row-span-2 md:[&>div]:row-span-1 [&>label]:row-span-2 md:[&>label]:row-span-1',
                    isInCartList && 'vl:col-start-1 vl:pl-22.5',
                )}
            >
                <Checkbox
                    disabled={isDisabled}
                    id={checkboxId}
                    label={<span aria-hidden="true" />}
                    labelWrapperClassName="w-fit items-start gap-0 font-normal text-text-default hover:text-text-default md:min-h-10 md:items-center"
                    name={checkboxId}
                    value={isSelected}
                    onChange={() => onToggleService(additionalService, !isSelected)}
                />

                <AdditionalServiceImageLabel
                    additionalService={additionalService}
                    checkboxId={checkboxId}
                    isDisabled={isDisabled}
                />

                <AdditionalServiceLabel
                    additionalService={additionalService}
                    checkboxId={checkboxId}
                    descriptionButton={
                        <AdditionalServiceDescriptionButton
                            additionalService={additionalService}
                            tidPrefix={tidPrefix}
                            onOpenDescription={onOpenDescription}
                        />
                    }
                    desktopPrice={desktopPrice}
                    isDisabled={isDisabled}
                    mobilePrice={mobilePrice}
                    tidPrefix={tidPrefix}
                />
            </div>

            {isSelectedInCart && (
                <div
                    className={twMergeCustom(
                        'col-start-3 row-start-2 flex min-w-0 items-center md:col-span-1 md:col-start-2 md:row-start-1 md:shrink-0 md:gap-2',
                        isDisabled && 'opacity-50',
                        'vl:contents',
                    )}
                >
                    <AdditionalServiceCartPrice priceWithVat={priceWithVat} quantity={quantity} unitName={unitName} />
                </div>
            )}
        </li>
    );
};
