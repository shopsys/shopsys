import { Flag } from 'components/Basic/Flag/Flag';
import { TypeStoreOpeningStatusEnum } from 'graphql/types';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type OpeningStatusProps = {
    status: TypeStoreOpeningStatusEnum;
    className?: string;
};

export const OpeningStatus: FC<OpeningStatusProps> = ({ status, className }) => {
    const { t } = useTranslation();

    const statusText = (status: TypeStoreOpeningStatusEnum): string => {
        switch (status) {
            case TypeStoreOpeningStatusEnum.Open:
                return t('Open');
            case TypeStoreOpeningStatusEnum.Closed:
                return t('Closed');
            case TypeStoreOpeningStatusEnum.OpenSoon:
                return t('Opening soon');
            case TypeStoreOpeningStatusEnum.ClosedSoon:
                return t('Closing soon');
            default:
                return t('Closed');
        }
    };

    const ariaLabel = t('Currently {{status}}', { status: statusText(status) });

    const statusClasses = {
        [TypeStoreOpeningStatusEnum.Open]: 'bg-opening-status-open',
        [TypeStoreOpeningStatusEnum.Closed]: 'bg-opening-status-closed',
        [TypeStoreOpeningStatusEnum.OpenSoon]: 'bg-opening-status-open-today',
        [TypeStoreOpeningStatusEnum.ClosedSoon]: 'bg-opening-status-open-today',
    };

    return (
        <Flag
            ariaLabel={ariaLabel}
            className={twMergeCustom('text-nowrap text-text-inverted', className, statusClasses[status])}
        >
            <div aria-hidden="true">{statusText(status)}</div>
        </Flag>
    );
};
