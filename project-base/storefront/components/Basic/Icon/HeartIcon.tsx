export const HeartIcon: SvgFC<{ isFull: boolean }> = ({ isFull, ...props }) => (
    <svg {...props} fill={isFull ? 'currentColor' : 'none'} viewBox="0 0 16 14" xmlns="http://www.w3.org/2000/svg">
        <path
            clipRule="evenodd"
            d="M7.99538 2.43711C6.64581 0.86435 4.39533 0.441282 2.70442 1.88146C1.01351 3.32164 0.775453 5.72955 2.10333 7.43286C3.20738 8.84904 6.5486 11.8359 7.64367 12.8026C7.76618 12.9108 7.82744 12.9648 7.89889 12.9861C7.96126 13.0046 8.0295 13.0046 8.09186 12.9861C8.16331 12.9648 8.22457 12.9108 8.34709 12.8026C9.44216 11.8359 12.7834 8.84904 13.8874 7.43286C15.2153 5.72955 15.0063 3.30649 13.2863 1.88146C11.5664 0.456431 9.34494 0.86435 7.99538 2.43711Z"
            fillRule="evenodd"
            stroke="currentColor"
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth="2"
        />
    </svg>
);
