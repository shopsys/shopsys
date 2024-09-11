import { Flag } from 'components/Basic/Flag/Flag';
import { TypeStoreOpeningStatusEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { twMergeCustom } from 'utils/twMerge';

type OpeningStatusProps = {
    status: TypeStoreOpeningStatusEnum;
    isDynamic?: boolean;
    className?: string;
};

export const OpeningStatus: FC<OpeningStatusProps> = ({ status, isDynamic = false, className }) => {
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

    const statusClasses = {
        [TypeStoreOpeningStatusEnum.Open]: 'bg-openingStatusOpen',
        [TypeStoreOpeningStatusEnum.Closed]: 'bg-openingStatusClosed',
        [TypeStoreOpeningStatusEnum.OpenSoon]: 'bg-openingStatusOpenToday',
        [TypeStoreOpeningStatusEnum.ClosedSoon]: 'bg-openingStatusOpenToday',
    };

    return (
        <Flag
            className={twMergeCustom('text-textInverted', className, statusClasses[status])}
            type={isDynamic ? 'dynamic' : 'custom'}
        >
            {statusText(status)}
        </Flag>
    );
};
