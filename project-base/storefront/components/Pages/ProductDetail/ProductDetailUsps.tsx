import { CheckmarkBadgeIcon } from 'components/Basic/Icon/CheckmarkBadgeIcon';

type ProductDetailUspsProps = {
    usps: string[];
};

export const ProductDetailUsps: FC<ProductDetailUspsProps> = ({ usps }) => {
    return (
        <ul className="flex flex-col gap-3">
            {usps.map((usp) => (
                <li key={usp} className="flex items-center gap-3 text-sm">
                    <CheckmarkBadgeIcon className="w-5 text-text-success" />
                    <span>{usp}</span>
                </li>
            ))}
        </ul>
    );
};
