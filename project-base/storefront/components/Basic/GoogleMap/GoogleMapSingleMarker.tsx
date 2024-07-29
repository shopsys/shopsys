import { GoogleMapMarkerIcon } from 'components/Basic/Icon/GoogleMapMarkerIcon';
import { twJoin } from 'tailwind-merge';

type GoogleMapSingleMarkerProps = {
    onClick: () => void;
    isActive: boolean;
    isDetail?: boolean;
};

export const GoogleMapSingleMarker: FC<GoogleMapSingleMarkerProps> = ({ isActive, isDetail, onClick }) => (
    <div className="absolute -translate-x-1/2 -translate-y-full" onClick={onClick}>
        <GoogleMapMarkerIcon
            className={twJoin(
                'w-10 h-10',
                isActive ? 'origin-bottom scale-125 text-primary' : 'text-secondary',
                isDetail ? 'cursor-default' : 'cursor-pointer',
            )}
        />
    </div>
);
