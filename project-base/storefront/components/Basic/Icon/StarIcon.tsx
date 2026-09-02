export const StarIcon: SvgFC = ({ fill = 'none', stroke = 'currentColor', ...props }) => (
    <svg {...props} fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path
            clipRule="evenodd"
            d="M12 17.235L6.179 20L7.388 13.88L3 9.392L9.179 8.621L12 3L14.821 8.621L21 9.392L16.612 13.88L17.821 20L12 17.235Z"
            fill={fill}
            fillRule="evenodd"
            stroke={stroke}
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth="1.5"
        />
    </svg>
);
