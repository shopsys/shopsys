import { GoogleMapMarkerIcon } from 'components/Basic/Icon/GoogleMapMarkerIcon';
import { AnyProps, PointFeature } from 'supercluster';
import { twJoin } from 'tailwind-merge';

const ClusterMarker: FC = ({ children }) => (
    <div className="absolute h-[30px] w-6 -translate-x-1/2 -translate-y-full text-backgroundBrand">
        <GoogleMapMarkerIcon className={twJoin('h-[30px] w-6')} />

        <div className="absolute inset-0 flex justify-center pt-1 text-xs font-bold text-textInverted">{children}</div>
    </div>
);

const SingleMarker: FC<{ onClick: () => void; isActive: boolean; isDetail?: boolean }> = ({
    isActive,
    isDetail,
    onClick,
}) => (
    <div className="absolute -translate-x-1/2 -translate-y-full" onClick={onClick}>
        <GoogleMapMarkerIcon
            isSingle
            className={twJoin(
                'h-[26px] w-5 text-backgroundBrand',
                isActive && 'origin-bottom scale-125',
                isDetail ? 'cursor-default' : 'cursor-pointer',
            )}
        />
    </div>
);

type GoogleMapMarkerProps = {
    activeMarkerIdentifier: string;
    cluster: PointFeature<AnyProps>;
    isDetail?: boolean;
    onMarkerClicked: (identifier: string) => void;
};

export const GoogleMapMarker: FC<GoogleMapMarkerProps> = ({
    activeMarkerIdentifier,
    cluster,
    isDetail,
    onMarkerClicked,
}) => {
    const { cluster: isCluster, point_count: pointCount, markerIdentifier } = cluster.properties;
    const isActive = markerIdentifier === activeMarkerIdentifier;

    if (isCluster) {
        return <ClusterMarker>{pointCount}</ClusterMarker>;
    }

    return <SingleMarker isActive={isActive} isDetail={isDetail} onClick={() => onMarkerClicked(markerIdentifier)} />;
};
