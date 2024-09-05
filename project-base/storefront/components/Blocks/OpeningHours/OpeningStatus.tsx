import { Flag } from 'components/Basic/Flag/Flag';
import useTranslation from 'next-translate/useTranslation';
import { twMergeCustom } from 'utils/twMerge';

export const OpeningStatus: FC<{ isOpen: boolean; isDynamic?: boolean }> = ({
    isOpen,
    isDynamic = false,
    className,
}) => {
    const { t } = useTranslation();

    return (
        <Flag
            type={isDynamic ? 'dynamic' : 'custom'}
            className={twMergeCustom(
                'text-textInverted',
                isOpen ? 'bg-openingStatusOpen' : 'bg-openingStatusClosed',
                className,
            )}
        >
            {isOpen ? t('Open') : t('Closed')}
        </Flag>
    );
};
