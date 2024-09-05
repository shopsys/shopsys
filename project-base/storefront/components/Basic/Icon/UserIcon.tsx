export const UserIcon: SvgFC<{ isFull: boolean }> = ({ isFull, ...props }) => (
    <svg xmlns="http://www.w3.org/2000/svg" {...props} fill={isFull ? 'currentColor' : 'none'} viewBox="0 0 25 25">
        <path
            d="M12.487 15.54a6.914 6.914 0 1 0 0-13.828 6.914 6.914 0 0 0 0 13.828zm0 0c-6.11 0-11.063 3.715-11.063 8.298m11.063-8.298c6.11 0 11.063 3.715 11.063 8.298"
            stroke="currentColor"
            strokeLinecap="round"
            strokeWidth="2.323"
        />
    </svg>
);
