import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import {
    AdditionalServiceImage,
    AdditionalServiceLabel,
    hasDeliveryExtension,
} from 'components/Blocks/Product/AdditionalServices/AdditionalServiceLabel';
import {
    AdditionalServiceAccessiblePrice,
    AdditionalServiceAddOnPrice,
    AdditionalServiceCartPrice,
} from 'components/Blocks/Product/AdditionalServices/AdditionalServicePrice';
import { IconButton } from 'components/Forms/Button/IconButton';
import { ChoiceIndicator } from 'components/Forms/Lib/ChoiceIndicator';
import { TIDs } from 'cypress/tids';
import { TypeAdditionalServiceFragment } from 'graphql/requests/additionalServices/fragments/AdditionalServiceFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible } from 'utils/mappers/price';
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
            // above the stretched label of the service
            className="relative z-above align-middle"
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
    const priceDescriptionId = `${checkboxId}-price`;
    const deliveryExtensionId = `${checkboxId}-delivery`;
    const checkboxDescriptionIds = [
        isPriceVisible(priceWithVat) && priceDescriptionId,
        hasDeliveryExtension(additionalService) && deliveryExtensionId,
    ].filter(Boolean);
    const isSelectedInCart = isInCartList === true && isSelected && quantity !== undefined;
    const isTotalPriceShown = showSelectedServiceTotalPrice === true && isSelected;
    const isUnitShown = isSelected
        ? !isTotalPriceShown
        : isInCartList === true || showSelectedServiceTotalPrice === true;
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
            // the name is the only label of the checkbox, the item is the hover scope of its indicator
            data-choice-hover-scope
            className={twMergeCustom(
                'relative grid min-w-0 grid-cols-[auto_auto_minmax(0,1fr)] items-center gap-x-2 md:grid-cols-[minmax(0,1fr)_auto] md:gap-3',
                isSelectedInCart && 'vl:contents',
                isInCartList && !isSelected && 'vl:col-start-1 vl:col-end-2',
            )}
        >
            <div
                className={twMergeCustom(
                    // positions the stretched label on desktop, where the item may be display: contents
                    'contents md:relative md:col-span-1 md:col-start-1 md:flex md:min-w-0 md:gap-2',
                    isSelectedInCart && '[&>div]:row-span-2 md:[&>div]:row-span-1',
                    isInCartList && 'vl:col-start-1 vl:pl-22.5',
                )}
            >
                <div className="flex items-center self-stretch md:min-h-10 md:self-auto">
                    <input
                        aria-describedby={checkboxDescriptionIds.join(' ') || undefined}
                        checked={isSelected}
                        className="peer sr-only"
                        disabled={isDisabled}
                        id={checkboxId}
                        name={checkboxId}
                        type="checkbox"
                        onChange={() => onToggleService(additionalService, !isSelected)}
                    />
                    <ChoiceIndicator
                        checked={isSelected}
                        className="peer-focus-visible:bg-focus-background! peer-focus-visible:[&_svg]:text-text-default!"
                        disabled={isDisabled}
                        inputType="checkbox"
                    />
                </div>

                <AdditionalServiceAccessiblePrice
                    id={priceDescriptionId}
                    isSelectedInCart={isSelectedInCart}
                    isTotalPriceShown={isTotalPriceShown}
                    priceWithVat={priceWithVat}
                    quantity={quantity}
                    showUnit={isUnitShown}
                    unitName={unitName}
                />

                <AdditionalServiceImage additionalService={additionalService} isDisabled={isDisabled} />

                <AdditionalServiceLabel
                    additionalService={additionalService}
                    checkboxId={checkboxId}
                    deliveryExtensionId={deliveryExtensionId}
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
