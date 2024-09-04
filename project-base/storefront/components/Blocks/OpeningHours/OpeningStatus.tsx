import { Flag } from 'components/Basic/Flag/Flag';
import { TypeStoreOpeningStatusEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { twMergeCustom } from 'utils/twMerge';

export const OpeningStatus: FC<{ status: TypeStoreOpeningStatusEnum; isDynamic?: boolean }> = ({
    status,
    isDynamic = false,
    className,
}) => {
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

    return (
        <Flag
            type={isDynamic ? 'dynamic' : 'custom'}
            className={twMergeCustom(
                'text-textInverted',
                status === TypeStoreOpeningStatusEnum.Open && 'bg-openingStatusOpen',
                status === TypeStoreOpeningStatusEnum.Closed && 'bg-openingStatusClosed',
                (status === TypeStoreOpeningStatusEnum.OpenSoon || status === TypeStoreOpeningStatusEnum.ClosedSoon) &&
                    'bg-openingStatusOpenToday',
                className,
            )}
        >
            {statusText(status)}
        </Flag>
    );
};
