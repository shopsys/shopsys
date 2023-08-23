import { twJoin } from 'tailwind-merge';
import { MapMarkerIcon } from '../Icon/IconsSvg';

interface SeznamMapMarkerIconProps {
    isActive: boolean;
    isClickable: boolean;
}

export const SeznamMapMarkerIcon: FC<SeznamMapMarkerIconProps> = ({ isActive, isClickable }) => (
    <MapMarkerIcon
        className={twJoin(
            'w-8 transition-transform',
            isActive ? 'origin-bottom scale-125 text-orange' : 'text-greyDark',
            isClickable ? 'cursor-pointer' : 'cursor-default',
        )}
    />
);
