import useTranslation from 'next-translate/useTranslation';
import { twMergeCustom } from 'utils/twMerge';

export const OpeningStatus: FC<{ isOpen: boolean }> = ({ isOpen, className }) => {
    const { t } = useTranslation();

    return (
        <div
            className={twMergeCustom(
                'inline-block rounded py-1 px-3 font-medium uppercase leading-normal text-textInverted',
                isOpen ? 'bg-openingStatusOpen' : 'bg-openingStatusClosed',
                className,
            )}
        >
            {isOpen ? t('Currently open') : t('Currently closed')}
        </div>
    );
};
