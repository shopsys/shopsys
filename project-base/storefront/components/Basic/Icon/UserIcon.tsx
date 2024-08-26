export const UserIcon: SvgFC<{ isFull: boolean }> = ({ isFull, ...props }) => (
    <svg {...props} fill={isFull ? 'currentColor' : 'none'} viewBox="0 0 25 25" xmlns="http://www.w3.org/2000/svg">
        <path
            d="M12.5 14.788a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 0c-4.418 0-8 2.686-8 6m8-6c4.418 0 8 2.686 8 6"
            stroke="currentColor"
            strokeLinecap="round"
            strokeWidth="2"
        />
    </svg>
);
