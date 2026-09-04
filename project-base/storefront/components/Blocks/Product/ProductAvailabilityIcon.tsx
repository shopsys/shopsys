import { CheckmarkDoneIcon } from 'components/Basic/Icon/CheckmarkDoneIcon';
import { ClockIcon } from 'components/Basic/Icon/ClockIcon';
import { PackageUnavailableIcon } from 'components/Basic/Icon/PackageUnavailableIcon';
import { TypeAvailabilityStatusEnum } from 'graphql/types';

type ProductAvailabilityIconProps = {
    status: TypeAvailabilityStatusEnum;
};

export const ProductAvailabilityIcon: FC<ProductAvailabilityIconProps> = ({ status, ...props }) => {
    const AvailabilityIcon = availabilityIconByStatus[status];

    return <AvailabilityIcon aria-hidden {...props} />;
};

const availabilityIconByStatus: Record<TypeAvailabilityStatusEnum, SvgFC> = {
    [TypeAvailabilityStatusEnum.InStock]: CheckmarkDoneIcon,
    [TypeAvailabilityStatusEnum.ExpectedRestock]: ClockIcon,
    [TypeAvailabilityStatusEnum.OutOfStock]: PackageUnavailableIcon,
};
