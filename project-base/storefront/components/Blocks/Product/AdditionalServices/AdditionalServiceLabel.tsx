import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import { TypeAdditionalServiceFragment } from 'graphql/requests/additionalServices/fragments/AdditionalServiceFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type AdditionalServiceLabelProps = {
    additionalService: TypeAdditionalServiceFragment;
    checkboxId: string;
    deliveryExtensionId: string;
    descriptionButton?: React.ReactNode;
    desktopPrice?: React.ReactNode;
    isDisabled?: boolean;
    mobilePrice?: React.ReactNode;
    tidPrefix: string;
};

export const hasDeliveryExtension = (additionalService: TypeAdditionalServiceFragment): boolean =>
    additionalService.deliveryDaysExtension !== null && additionalService.deliveryDaysExtension > 0;

type AdditionalServiceImageProps = Pick<AdditionalServiceLabelProps, 'additionalService' | 'isDisabled'>;

export const AdditionalServiceImage: FC<AdditionalServiceImageProps> = ({ additionalService, isDisabled }) =>
    additionalService.mainImage ? (
        <div className={twMergeCustom('flex items-center', isDisabled && 'opacity-50')}>
            <Image
                alt=""
                className="size-8 shrink-0 object-contain mix-blend-multiply"
                height={32}
                src={additionalService.mainImage.url}
                width={32}
            />
        </div>
    ) : null;

export const AdditionalServiceLabel: FC<AdditionalServiceLabelProps> = ({
    additionalService,
    checkboxId,
    deliveryExtensionId,
    descriptionButton,
    desktopPrice,
    isDisabled,
    mobilePrice,
    tidPrefix,
}) => {
    const { t } = useTranslation();

    return (
        <span className="col-start-3 flex min-w-0 flex-1 flex-col items-start justify-center text-sm md:col-auto">
            <span className="flex w-fit min-w-0 max-w-full items-center gap-2">
                <label
                    className={twMergeCustom(
                        'wrap-break-words w-fit max-w-full cursor-pointer font-ui',
                        // the only label of the checkbox, stretched over the whole item, so the checkbox and the image toggle it too
                        'after:absolute after:inset-0',
                        !isDisabled && 'choice-hover:underline',
                        isDisabled && 'cursor-no-drop opacity-50',
                    )}
                    data-tid={TIDs.additional_services_checkbox_ + tidPrefix + additionalService.catnum}
                    htmlFor={checkboxId}
                >
                    {additionalService.name}
                </label>

                {desktopPrice && <span className="hidden md:inline">{desktopPrice}</span>}
                {descriptionButton}
            </span>

            {hasDeliveryExtension(additionalService) && (
                <span
                    className={twMergeCustom('w-fit max-w-full text-text-less text-xs', isDisabled && 'opacity-50')}
                    id={deliveryExtensionId}
                >
                    {t('Extends delivery by {{ count }} working days', {
                        count: additionalService.deliveryDaysExtension,
                    })}
                </span>
            )}

            {mobilePrice && (
                <span className={twMergeCustom('w-fit md:hidden', isDisabled && 'opacity-50')}>{mobilePrice}</span>
            )}
        </span>
    );
};
