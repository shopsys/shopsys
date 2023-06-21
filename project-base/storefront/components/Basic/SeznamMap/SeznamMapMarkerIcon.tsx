import { Icon } from '../Icon/Icon';
import { twJoin } from 'tailwind-merge';

interface SeznamMapMarkerIconProps {
    isActive: boolean;
    isClickable: boolean;
}

export const SeznamMapMarkerIcon: FC<SeznamMapMarkerIconProps> = ({ isActive, isClickable }) => (
    <Icon
        iconType="icon"
        icon="MapMarker"
        className={twJoin(
            'w-8 transition-transform',
            isActive ? 'origin-bottom scale-125 text-orange' : 'text-greyDark',
            isClickable ? 'cursor-pointer' : 'cursor-default',
        )}
    />
);
