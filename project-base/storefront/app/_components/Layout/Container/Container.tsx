import { Webline } from 'components/Layout/Webline/Webline';
import { twJoin } from 'tailwind-merge';

type ContainerProps = {
    heading?: string | null;
    headingTid?: string;
    standardWidth?: boolean;
    gap?: 'small' | 'medium' | 'large';
};

export const Container: FC<ContainerProps> = ({
    heading,
    headingTid,
    children,
    standardWidth = true,
    gap = 'medium',
}) => {
    const gapClasses = {
        small: 'gap-4',
        medium: 'gap-5 xl:gap-8',
        large: 'gap-6 xl:gap-10',
    };

    return (
        <>
            {standardWidth ? (
                <Webline>
                    {heading && (
                        <h1 className="mb-4" tid={headingTid}>
                            {heading}
                        </h1>
                    )}
                    <section className={twJoin('flex flex-col', gapClasses[gap])}>{children}</section>
                </Webline>
            ) : (
                <section className="mx-auto w-full max-w-3xl">
                    {heading && (
                        <h1 className="mb-4" tid={headingTid}>
                            {heading}
                        </h1>
                    )}
                    <section className={twJoin('flex w-full flex-col', gapClasses[gap])}>{children}</section>
                </section>
            )}
        </>
    );
};
