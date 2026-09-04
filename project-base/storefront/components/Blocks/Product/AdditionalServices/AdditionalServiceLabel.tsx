import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import { TypeAdditionalServiceFragment } from 'graphql/requests/additionalServices/fragments/AdditionalServiceFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type AdditionalServiceLabelProps = {
    additionalService: TypeAdditionalServiceFragment;
    checkboxId: string;
    descriptionButton?: React.ReactNode;
    desktopPrice?: React.ReactNode;
    isDisabled?: boolean;
    mobilePrice?: React.ReactNode;
    tidPrefix: string;
};

type AdditionalServiceImageLabelProps = Pick<
    AdditionalServiceLabelProps,
    'additionalService' | 'checkboxId' | 'isDisabled'
>;

export const AdditionalServiceImageLabel: FC<AdditionalServiceImageLabelProps> = ({
    additionalService,
    checkboxId,
    isDisabled,
}) =>
    additionalService.mainImage ? (
        <label
            className={twMergeCustom('flex cursor-pointer items-center', isDisabled && 'cursor-no-drop opacity-50')}
            htmlFor={checkboxId}
        >
            <Image
                alt=""
                className="size-8 shrink-0 object-contain mix-blend-multiply"
                height={32}
                src={additionalService.mainImage.url}
                width={32}
            />
        </label>
    ) : null;

export const AdditionalServiceLabel: FC<AdditionalServiceLabelProps> = ({
    additionalService,
    checkboxId,
    descriptionButton,
    desktopPrice,
    isDisabled,
    mobilePrice,
    tidPrefix,
}) => {
    const { t } = useTranslation();

    return (
        <span className="col-start-3 flex min-w-0 flex-1 flex-col items-start justify-center font-secondary text-sm md:col-auto">
            <span className="flex w-fit min-w-0 max-w-full items-center gap-2">
                <label
                    className={twMergeCustom(
                        'wrap-break-words w-fit max-w-full cursor-pointer font-semibold',
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

            {additionalService.deliveryDaysExtension !== null && additionalService.deliveryDaysExtension > 0 && (
                <label
                    className={twMergeCustom(
                        'w-fit max-w-full cursor-pointer text-text-less text-xs',
                        isDisabled && 'cursor-no-drop opacity-50',
                    )}
                    htmlFor={checkboxId}
                >
                    {t('Extends delivery by {{ count }} working days', {
                        count: additionalService.deliveryDaysExtension,
                    })}
                </label>
            )}

            {mobilePrice && (
                <span className={twMergeCustom('w-fit md:hidden', isDisabled && 'opacity-50')}>{mobilePrice}</span>
            )}
        </span>
    );
};
