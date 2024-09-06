export const GoogleMapMarkerIcon: SvgFC<{ isSingle?: boolean }> = ({ isSingle = false, ...props }) => (
    <svg {...props} viewBox="0 0 20 26" xmlns="http://www.w3.org/2000/svg">
        <path
            d="M10 26C12.5 20.8 20 17.4438 20 10.4C20 4.65624 15.5228 0 10 0C4.47715 0 0 4.65624 0 10.4C0 17.4438 7.5 20.8 10 26Z"
            fill="currentColor"
        />
        {isSingle && (
            <path
                d="M10 14C12.2091 14 14 12.2091 14 10C14 7.79086 12.2091 6 10 6C7.79086 6 6 7.79086 6 10C6 12.2091 7.79086 14 10 14Z"
                fill="white"
            />
        )}
    </svg>
);
