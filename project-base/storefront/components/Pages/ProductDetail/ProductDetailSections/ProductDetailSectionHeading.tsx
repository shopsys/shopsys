import { twMergeCustom } from 'utils/twMerge';

type ProductDetailSectionHeadingProps = {
    className?: string;
    children: string;
};

export const ProductDetailSectionHeading = ({ className, children }: ProductDetailSectionHeadingProps) => {
    return <h2 className={twMergeCustom('h3 mb-4', className)}>{children}</h2>;
};
