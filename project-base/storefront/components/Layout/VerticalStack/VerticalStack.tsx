import { twJoin } from 'tailwind-merge';

type VerticalStackProps = {
    gap: 'xs' | 'sm' | 'md' | 'lg';
};

export const VerticalStack: FC<VerticalStackProps> = ({ gap, children }) => {
    const gapClasses = {
        xs: 'gap-4',
        sm: 'gap-5',
        md: 'gap-5 xl:gap-8',
        lg: 'gap-6 xl:gap-10',
    };

    return <section className={twJoin('flex flex-col', gapClasses[gap])}>{children}</section>;
};
