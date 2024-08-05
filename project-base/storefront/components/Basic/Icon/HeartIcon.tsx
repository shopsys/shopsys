export const HeartIcon: SvgFC<{ isFull: boolean }> = ({ isFull, ...props }) => (
    <svg {...props} viewBox="0 0 23 19" xmlns="http://www.w3.org/2000/svg">
        <path
            clipRule="evenodd"
            d="M20.046 2.591a5.43 5.43 0 0 0-7.681 0l-1.047 1.047-1.046-1.047a5.431 5.431 0 0 0-7.681 7.681l1.046 1.047 6.267 6.267a2 2 0 0 0 2.829 0L19 11.319l1.046-1.047a5.43 5.43 0 0 0 0-7.68Z"
            fill={isFull ? 'currentColor' : 'transparent'}
            stroke="currentColor"
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth="2.5"
        />
    </svg>
);
