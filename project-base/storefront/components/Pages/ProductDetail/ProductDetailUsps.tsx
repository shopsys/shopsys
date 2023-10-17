type ProductDetailUspsProps = {
    usps: string[];
};

export const ProductDetailUsps: FC<ProductDetailUspsProps> = ({ usps }) => {
    return (
        <ul className="grid grid-cols-2 gap-y-1 gap-x-8 pl-4">
            {usps.map((usp, index) => (
                <li key={index} className="list-outside list-disc">
                    {usp}
                </li>
            ))}
        </ul>
    );
};
