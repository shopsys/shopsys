import { GoogleMapMarkerIcon } from 'components/Basic/Icon/GoogleMapMarkerIcon';
import { AnyProps, PointFeature } from 'supercluster';
import { twJoin } from 'tailwind-merge';
import { MapMarker } from 'types/map';

const ClusterMarker: FC<{ onClick: () => void }> = ({ onClick, children }) => {
    return (
        <button
            className="absolute h-[30px] w-6 -translate-x-1/2 -translate-y-full text-background-brand"
            title={`Cluster of ${children} locations`}
            type="button"
            onClick={onClick}
        >
            <GoogleMapMarkerIcon className={twJoin('h-[30px] w-6')} />

            <span className="absolute inset-0 flex justify-center pt-1 font-bold text-text-inverted text-xs">
                {children}
            </span>
        </button>
    );
};

const SingleMarker: FC<{ onClick: () => void; isActive: boolean; isDetail?: boolean }> = ({
    isActive,
    isDetail,
    onClick,
}) => {
    return (
        <button
            aria-current={isActive ? 'true' : false}
            className="absolute -translate-x-1/2 -translate-y-full"
            tabIndex={0}
            title="Location marker"
            type="button"
            onClick={onClick}
        >
            <GoogleMapMarkerIcon
                isSingle
                className={twJoin(
                    'h-[26px] w-5 text-background-brand',
                    isActive && 'origin-bottom scale-125',
                    isDetail ? 'cursor-default' : 'cursor-pointer',
                )}
            />
        </button>
    );
};

type GoogleMapMarkerProps = {
    activeMarkerIdentifier: string;
    cluster: PointFeature<AnyProps>;
    isDetail?: boolean;
    onMarkerClicked: (marker: MapMarker) => void;
    onClusterClicked: (cluster: any) => void;
};

export const GoogleMapMarker: FC<GoogleMapMarkerProps> = ({
    activeMarkerIdentifier,
    cluster,
    isDetail,
    onMarkerClicked,
    onClusterClicked,
}) => {
    const { cluster: isCluster, marker, markerIdentifier, point_count: pointCount } = cluster.properties;
    const isActive = markerIdentifier === activeMarkerIdentifier;

    if (isCluster) {
        return <ClusterMarker onClick={() => onClusterClicked(cluster)}>{pointCount}</ClusterMarker>;
    }

    if (!marker) {
        return null;
    }

    return <SingleMarker isActive={isActive} isDetail={isDetail} onClick={() => onMarkerClicked(marker)} />;
};
