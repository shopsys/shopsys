import { GoogleMapMarkerIcon } from 'components/Basic/Icon/GoogleMapMarkerIcon';
import { GoogleMapSearchMarkerIcon } from 'components/Basic/Icon/GoogleMapSearchMarkerIcon';
import { Tooltip } from 'components/Basic/Tooltip/Tooltip';
import { AnyProps, PointFeature } from 'supercluster';
import { twJoin } from 'tailwind-merge';
import { MapMarker } from 'types/map';
import useTranslation from 'utils/i18n/useTranslationWrapper';

const ClusterMarker: FC<{ count: number; onClick: () => void }> = ({ count, onClick }) => {
    const { t } = useTranslation();
    const label = t('Zoom to {{ count }} locations', { count });

    return (
        <Tooltip label={label}>
            <button
                aria-label={label}
                className="absolute size-11 -translate-x-1/2 -translate-y-full text-background-brand"
                type="button"
                onClick={onClick}
            >
                <GoogleMapMarkerIcon className="size-11" />

                <span className="absolute -top-1 right-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-icon-accent px-0.5 text-text-inverted text-xs leading-normal">
                    {count}
                </span>
            </button>
        </Tooltip>
    );
};

const SingleMarker: FC<{ onClick: () => void; isActive: boolean; isDetail?: boolean; title: string }> = ({
    isActive,
    isDetail,
    onClick,
    title,
}) => {
    return (
        <Tooltip label={title}>
            <button
                aria-current={isActive ? 'true' : false}
                aria-label={title}
                className="absolute -translate-x-1/2 -translate-y-full"
                tabIndex={0}
                type="button"
                onClick={onClick}
            >
                <GoogleMapMarkerIcon
                    isSingle
                    className={twJoin(
                        'size-11 text-background-brand',
                        isActive && 'origin-bottom scale-125',
                        isDetail ? 'cursor-default' : 'cursor-pointer',
                    )}
                />
            </button>
        </Tooltip>
    );
};

type SearchMarkerProps = {
    lat: number;
    lng: number;
    title: string;
};

export const GoogleMapSearchMarker: FC<SearchMarkerProps> = ({ title }) => (
    <div className="absolute -translate-x-1/2 -translate-y-full" title={title}>
        <GoogleMapSearchMarkerIcon className="size-11 text-price-discounted" />
    </div>
);

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
        return <ClusterMarker count={pointCount} onClick={() => onClusterClicked(cluster)} />;
    }

    if (!marker) {
        return null;
    }

    return (
        <SingleMarker
            isActive={isActive}
            isDetail={isDetail}
            title={marker.name || 'Location marker'}
            onClick={() => onMarkerClicked(marker)}
        />
    );
};
