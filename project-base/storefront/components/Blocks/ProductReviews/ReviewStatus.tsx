import { CheckmarkRoundIcon } from 'components/Basic/Icon/CheckmarkRoundIcon';
import { ClockIcon } from 'components/Basic/Icon/ClockIcon';
import { CloseRoundIcon } from 'components/Basic/Icon/CloseRoundIcon';
import { StatusBadge, StatusBadgeVariant } from 'components/Basic/StatusBadge/StatusBadge';
import { TypeProductReviewStatusEnum } from 'graphql/types';
import { type ElementType } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ReviewStatusProps = {
    status: TypeProductReviewStatusEnum;
};

export const ReviewStatus: FC<ReviewStatusProps> = ({ status }) => {
    const { t } = useTranslation();

    const statusLabels: Record<TypeProductReviewStatusEnum, string> = {
        [TypeProductReviewStatusEnum.Pending]: t('Under review'),
        [TypeProductReviewStatusEnum.Approved]: t('Published'),
        [TypeProductReviewStatusEnum.Rejected]: t('Not published'),
    };

    const statusVariants: Record<TypeProductReviewStatusEnum, StatusBadgeVariant> = {
        [TypeProductReviewStatusEnum.Pending]: 'warning',
        [TypeProductReviewStatusEnum.Approved]: 'success',
        [TypeProductReviewStatusEnum.Rejected]: 'error',
    };

    const statusIcons: Record<TypeProductReviewStatusEnum, ElementType> = {
        [TypeProductReviewStatusEnum.Pending]: ClockIcon,
        [TypeProductReviewStatusEnum.Approved]: CheckmarkRoundIcon,
        [TypeProductReviewStatusEnum.Rejected]: CloseRoundIcon,
    };

    return (
        <StatusBadge icon={statusIcons[status]} variant={statusVariants[status]}>
            {statusLabels[status]}
        </StatusBadge>
    );
};
