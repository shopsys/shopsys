import NextLink from 'next/link';
import { twJoin } from 'tailwind-merge';

type AdvancedSeoCategoriesItemProps = { slug: string };

export const AdvancedSeoCategoriesItem: FC<AdvancedSeoCategoriesItemProps> = ({ children, slug, className }) => (
    <NextLink href={slug} passHref>
        <a
            className={twJoin(
                'mb-3 flex items-center justify-center rounded-xl bg-greyVeryLight p-3 text-center text-sm text-dark no-underline lg:mr-6',
                'hover:bg-whitesmoke hover:text-dark hover:no-underline',
                'active:bg-whitesmoke active:text-dark active:no-underline ',
                className,
            )}
        >
            {children}
        </a>
    </NextLink>
);
