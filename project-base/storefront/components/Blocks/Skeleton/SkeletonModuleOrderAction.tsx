import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { SkeletonButton } from 'components/Basic/Skeleton/SkeletonButton';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type SkeletonModuleOrderActionProps = {
    buttonNext: string;
};

/**
 * Mirrors OrderAction, so the Back and next-step placeholders wrap into the same rows as the loaded buttons.
 */
export const SkeletonModuleOrderAction: FC<SkeletonModuleOrderActionProps> = ({ buttonNext }) => {
    const { t } = useTranslation();

    return (
        <div className="my-5 flex flex-wrap-reverse items-center justify-between gap-4 md:my-10">
            <SkeletonButton size="xlarge">
                <ArrowSecondaryIcon className="size-3 rotate-90" />
                {t('Back')}
            </SkeletonButton>

            <SkeletonButton size="xlarge">
                <span>{buttonNext}</span>

                <ArrowSecondaryIcon className="size-4 -rotate-90" />
            </SkeletonButton>
        </div>
    );
};
