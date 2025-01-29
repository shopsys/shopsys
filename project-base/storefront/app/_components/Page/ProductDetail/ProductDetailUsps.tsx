import { CheckmarkBadgeIcon } from 'components/Basic/Icon/CheckmarkBadgeIcon';

type ProductDetailUspsProps = {
    usps: string[];
};

export const ProductDetailUsps: FC<ProductDetailUspsProps> = ({ usps }) => {
    return (
        <ul className="flex flex-col gap-3">
            {usps.map((usp, index) => (
                <li key={index} className="flex items-center gap-3 text-sm">
                    <CheckmarkBadgeIcon className="w-5 text-textSuccess" />
                    <span>{usp}</span>
                </li>
            ))}
        </ul>
    );
};
