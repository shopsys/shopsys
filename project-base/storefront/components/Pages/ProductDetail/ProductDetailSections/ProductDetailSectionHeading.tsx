import { twMergeCustom } from 'utils/twMerge';

type ProductDetailSectionHeadingProps = {
    className?: string;
    children: string;
};

export const ProductDetailSectionHeading = ({ className, children }: ProductDetailSectionHeadingProps) => {
    return <h2 className={twMergeCustom('mb-4 text-center', className)}>{children}</h2>;
};
