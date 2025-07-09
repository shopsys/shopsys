import { GoogleMapMarkerIcon } from 'components/Basic/Icon/GoogleMapMarkerIcon';
import { AnyProps, PointFeature } from 'supercluster';
import { twJoin } from 'tailwind-merge';

const ClusterMarker: FC<{ onClick: () => void }> = ({ onClick, children }) => {
    return (
        <button
            className="text-background-brand absolute h-[30px] w-6 -translate-x-1/2 -translate-y-full"
            title={`Cluster of ${children} locations`}
            type="button"
            onClick={onClick}
        >
            <GoogleMapMarkerIcon className={twJoin('h-[30px] w-6')} />

            <span className="text-text-inverted absolute inset-0 flex justify-center pt-1 text-xs font-bold">
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
                    'text-background-brand h-[26px] w-5',
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
    onMarkerClicked: (identifier: string) => void;
    onClusterClicked: (cluster: any) => void;
};

export const GoogleMapMarker: FC<GoogleMapMarkerProps> = ({
    activeMarkerIdentifier,
    cluster,
    isDetail,
    onMarkerClicked,
    onClusterClicked,
}) => {
    const { cluster: isCluster, point_count: pointCount, markerIdentifier } = cluster.properties;
    const isActive = markerIdentifier === activeMarkerIdentifier;

    if (isCluster) {
        return <ClusterMarker onClick={() => onClusterClicked(cluster)}>{pointCount}</ClusterMarker>;
    }

    return <SingleMarker isActive={isActive} isDetail={isDetail} onClick={() => onMarkerClicked(markerIdentifier)} />;
};
