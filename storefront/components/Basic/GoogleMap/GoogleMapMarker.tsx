import { Icon } from '../Icon/Icon';
import { twJoin } from 'tailwind-merge';

interface GoogleMapMarkerProps {
    lat: number | null;
    lng: number | null;
    onClick: () => void;
    isActive: boolean;
    isDetail?: boolean;
}

export const GoogleMapMarker: FC<GoogleMapMarkerProps> = ({ isActive, isDetail, ...props }) => (
    <div className="absolute -translate-x-1/2 -translate-y-full" {...props}>
        <Icon
            iconType="icon"
            icon="MapMarker"
            width={isActive ? 40 : 32}
            height={isActive ? 48 : 38}
            className={twJoin(isActive ? 'text-orange' : 'text-greyDark ', !!isDetail && 'h-14 w-12 cursor-default')}
        />
    </div>
);
