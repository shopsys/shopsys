import { GoogleMapSingleMarker } from './GoogleMapSingleMarker';
import { AnyProps, PointFeature } from 'supercluster';

const ClusterMarker: FC = ({ children }) => (
    <div
        className={`absolute -translate-x-1/2 -translate-y-1/2 flex justify-center items-center w-12 h-12
          bg-white border-8 border-black border-opacity-40 rounded-full font-bold`}
    >
        {children}
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
    const { cluster: isCluster, point_count: pointCount, markerId } = cluster.properties;
    const isActive = cluster.properties.markerId === activeMarkerIdentifier;

    if (isCluster) {
        return <ClusterMarker key={cluster.id}>{pointCount}</ClusterMarker>;
    }

    return (
        <GoogleMapSingleMarker
            key={markerId}
            isActive={isActive}
            isDetail={isDetail}
            onClick={() => onMarkerClicked(cluster.properties.markerId)}
        />
    );
};
