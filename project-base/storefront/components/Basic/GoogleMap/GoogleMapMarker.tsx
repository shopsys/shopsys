import { GoogleMapMarkerIcon } from 'components/Basic/Icon/GoogleMapMarkerIcon';
import { AnyProps, PointFeature } from 'supercluster';
import { twJoin } from 'tailwind-merge';

const ClusterMarker: FC<{ onClick: () => void }> = ({ onClick, children }) => (
    <div className="absolute -translate-x-1/2 -translate-y-full w-6 h-[30px] text-backgroundBrand" onClick={onClick}>
        <GoogleMapMarkerIcon className={twJoin('w-6 h-[30px]')} />

        <div className="absolute inset-0 flex pt-1 justify-center text-textInverted text-xs font-bold">{children}</div>
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
                'w-5 h-[26px] text-backgroundBrand',
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
