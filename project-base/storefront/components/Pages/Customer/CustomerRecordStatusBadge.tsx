import { StatusBadge, StatusBadgeVariant } from 'components/Basic/StatusBadge/StatusBadge';
import { TypeComplaintStatusEnum, TypeOrderStatusEnum } from 'graphql/types';

type CustomerRecordStatus = TypeOrderStatusEnum | TypeComplaintStatusEnum;

const statusVariants: Record<CustomerRecordStatus, StatusBadgeVariant> = {
    [TypeOrderStatusEnum.New]: 'info',
    [TypeOrderStatusEnum.InProgress]: 'warning',
    [TypeComplaintStatusEnum.InProgress]: 'warning',
    [TypeOrderStatusEnum.Done]: 'success',
    [TypeComplaintStatusEnum.Resolved]: 'success',
    [TypeOrderStatusEnum.Canceled]: 'error',
    [TypeOrderStatusEnum.Withdrawn]: 'neutral',
};

export const CustomerRecordStatusBadge: FC<{ status: string; statusType: CustomerRecordStatus }> = ({
    status,
    statusType,
}) => (
    <StatusBadge className="max-w-full text-wrap break-words" variant={statusVariants[statusType]}>
        {status}
    </StatusBadge>
);
