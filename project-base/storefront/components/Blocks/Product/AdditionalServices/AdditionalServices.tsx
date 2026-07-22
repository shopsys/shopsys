import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { AdditionalServiceItem } from 'components/Blocks/Product/AdditionalServices/AdditionalServiceItem';
import { Button } from 'components/Forms/Button/Button';
import { TIDs } from 'cypress/tids';
import { AnimatePresence } from 'framer-motion';
import { TypeAdditionalServiceFragment } from 'graphql/requests/additionalServices/fragments/AdditionalServiceFragment.generated';
import dynamic from 'next/dynamic';
import { useEffect, useId, useRef, useState } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

const AdditionalServiceDescriptionPopup = dynamic(() =>
    import('components/Blocks/Product/AdditionalServices/AdditionalServiceDescriptionPopup').then(
        (component) => component.AdditionalServiceDescriptionPopup,
    ),
);

const MAX_INITIALLY_VISIBLE_SERVICES = 4;

type AdditionalServicesProps = {
    additionalServices: TypeAdditionalServiceFragment[];
    selectedServiceUuids: string[];
    onToggleService: (additionalService: TypeAdditionalServiceFragment, isSelected: boolean) => void;
    unitName: string;
    quantity?: number;
    isInCartList?: boolean;
    isDisabled?: boolean;
    showSelectedServiceTotalPrice?: boolean;
    className?: string;
    tidDiscriminator?: string;
};

export const AdditionalServices: FC<AdditionalServicesProps> = ({
    additionalServices,
    selectedServiceUuids,
    onToggleService,
    quantity,
    unitName,
    isInCartList,
    isDisabled,
    showSelectedServiceTotalPrice,
    className,
    tidDiscriminator,
}) => {
    const { t } = useTranslation();
    const [areAllServicesShown, setAreAllServicesShown] = useState(false);
    const [descriptionPopupAdditionalService, setDescriptionPopupAdditionalService] =
        useState<TypeAdditionalServiceFragment | null>(null);
    const [pendingSelectionByUuid, setPendingSelectionByUuid] = useState<Record<string, boolean>>({});
    const descriptionPopupTriggerRef = useRef<HTMLElement | null>(null);
    const checkboxInstanceId = useId();

    useEffect(() => {
        setPendingSelectionByUuid((currentPendingSelectionByUuid) => {
            const pendingEntries = Object.entries(currentPendingSelectionByUuid).filter(
                ([serviceUuid, pendingIsSelected]) =>
                    isDisabled && selectedServiceUuids.includes(serviceUuid) !== pendingIsSelected,
            );

            return pendingEntries.length === Object.keys(currentPendingSelectionByUuid).length
                ? currentPendingSelectionByUuid
                : Object.fromEntries(pendingEntries);
        });
    }, [isDisabled, selectedServiceUuids]);

    if (additionalServices.length === 0) {
        return null;
    }

    const tidPrefix = tidDiscriminator ? `${tidDiscriminator}_` : '';
    const isServiceSelected = (serviceUuid: string) =>
        pendingSelectionByUuid[serviceUuid] ?? selectedServiceUuids.includes(serviceUuid);
    const initiallyVisibleServices = additionalServices.slice(0, MAX_INITIALLY_VISIBLE_SERVICES);
    const additionalServicesAfterInitial = additionalServices.slice(MAX_INITIALLY_VISIBLE_SERVICES);
    const selectedAdditionalServicesAfterInitial = additionalServicesAfterInitial.filter((additionalService) =>
        isServiceSelected(additionalService.uuid),
    );
    const hiddenServices = additionalServicesAfterInitial.filter(
        (additionalService) => !isServiceSelected(additionalService.uuid),
    );
    const additionalServicesAfterInitialToShow = areAllServicesShown
        ? additionalServicesAfterInitial
        : selectedAdditionalServicesAfterInitial;
    const hasHiddenServices = hiddenServices.length > 0;
    const hiddenServicesContainerId = `additional-services-${checkboxInstanceId}-hidden`;

    const openDescriptionPopup = (additionalService: TypeAdditionalServiceFragment) => {
        if (!additionalService.description) {
            return;
        }

        if (document.activeElement instanceof HTMLElement) {
            descriptionPopupTriggerRef.current = document.activeElement;
        }

        setDescriptionPopupAdditionalService(additionalService);
    };

    const closeDescriptionPopup = () => {
        setDescriptionPopupAdditionalService(null);
        descriptionPopupTriggerRef.current?.focus();
        descriptionPopupTriggerRef.current = null;
    };

    const handleToggleService = (additionalService: TypeAdditionalServiceFragment, isSelected: boolean) => {
        setPendingSelectionByUuid((currentPendingSelectionByUuid) => ({
            ...currentPendingSelectionByUuid,
            [additionalService.uuid]: isSelected,
        }));
        onToggleService(additionalService, isSelected);
    };

    const renderAdditionalService = (additionalService: TypeAdditionalServiceFragment) => (
        <AdditionalServiceItem
            additionalService={additionalService}
            checkboxInstanceId={checkboxInstanceId}
            isDisabled={isDisabled}
            isInCartList={isInCartList}
            isSelected={isServiceSelected(additionalService.uuid)}
            key={additionalService.uuid}
            quantity={quantity}
            showSelectedServiceTotalPrice={showSelectedServiceTotalPrice}
            tidPrefix={tidPrefix}
            unitName={unitName}
            onOpenDescription={openDescriptionPopup}
            onToggleService={handleToggleService}
        />
    );

    return (
        <div
            className={twMergeCustom('flex min-w-0 flex-col gap-3', isInCartList && 'vl:contents', className)}
            data-tid={tidDiscriminator ? `${TIDs.additional_services}_${tidDiscriminator}` : TIDs.additional_services}
        >
            <span
                className={twMergeCustom(
                    'font-semibold text-sm',
                    isInCartList && 'vl:col-start-1 vl:mt-2 mb-2 vl:pl-22.5',
                )}
            >
                {t('Additional services')}
            </span>

            <div className={twMergeCustom(isInCartList && 'vl:contents')}>
                <ul
                    className={twMergeCustom('flex flex-col gap-4 md:gap-2', isInCartList && 'vl:contents')}
                    id={isInCartList ? hiddenServicesContainerId : undefined}
                >
                    {(isInCartList
                        ? [...initiallyVisibleServices, ...additionalServicesAfterInitialToShow]
                        : initiallyVisibleServices
                    ).map(renderAdditionalService)}
                </ul>

                {!isInCartList && (
                    <div id={hiddenServicesContainerId}>
                        <AnimatePresence initial={false}>
                            {additionalServicesAfterInitialToShow.length > 0 && (
                                <AnimateCollapseDiv className="block!" keyName="hidden-additional-services">
                                    <ul className="flex flex-col gap-4 pt-4 md:gap-2 md:pt-2">
                                        {additionalServicesAfterInitialToShow.map((additionalService) =>
                                            renderAdditionalService(additionalService),
                                        )}
                                    </ul>
                                </AnimateCollapseDiv>
                            )}
                        </AnimatePresence>
                    </div>
                )}
            </div>

            {hasHiddenServices && (
                <Button
                    aria-controls={hiddenServicesContainerId}
                    aria-expanded={areAllServicesShown}
                    className={twMergeCustom(
                        'gap-1 self-start px-2',
                        isInCartList && 'vl:col-start-1 vl:ml-22.5 vl:justify-self-start',
                    )}
                    size="small"
                    tid={
                        tidDiscriminator
                            ? `${TIDs.additional_services_expand_button}_${tidDiscriminator}`
                            : TIDs.additional_services_expand_button
                    }
                    variant="tertiary"
                    onClick={() => setAreAllServicesShown((previousValue) => !previousValue)}
                >
                    <span>
                        {areAllServicesShown
                            ? t('Show less')
                            : t('+ {{ count }} additional services', { count: hiddenServices.length })}
                    </span>
                    <ArrowIcon
                        aria-hidden="true"
                        className={twMergeCustom('size-4 transition-transform', areAllServicesShown && 'rotate-180')}
                    />
                </Button>
            )}

            {descriptionPopupAdditionalService?.description && (
                <AdditionalServiceDescriptionPopup
                    description={descriptionPopupAdditionalService.description}
                    name={descriptionPopupAdditionalService.name}
                    onClose={closeDescriptionPopup}
                />
            )}
        </div>
    );
};
