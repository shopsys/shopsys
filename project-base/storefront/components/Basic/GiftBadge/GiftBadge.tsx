import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type GiftBadgeProps = {
    className?: string;
};

export const GiftBadge: FC<GiftBadgeProps> = ({ className }) => {
    const { t } = useTranslation();

    return (
        <div
            className={twMergeCustom(
                'absolute top-0 left-0 z-10 rounded-tl-xl rounded-br-md bg-linear-to-r from-purple-600 to-pink-600 px-2 py-0.5 font-semibold text-white text-xs shadow-md',
                className,
            )}
        >
            {t('Gift')}
        </div>
    );
};
