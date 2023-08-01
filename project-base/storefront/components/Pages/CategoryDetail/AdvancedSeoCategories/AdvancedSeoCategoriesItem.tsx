import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { twJoin } from 'tailwind-merge';

type AdvancedSeoCategoriesItemProps = { slug: string };

export const AdvancedSeoCategoriesItem: FC<AdvancedSeoCategoriesItemProps> = ({ children, slug, className }) => (
    <ExtendedNextLink
        href={`/${slug}`}
        type="static"
        className={twJoin(
            'flex items-center justify-center rounded-xl bg-greyVeryLight p-3 text-center text-sm text-dark no-underline',
            'hover:bg-whitesmoke hover:text-dark hover:no-underline',
            'active:bg-whitesmoke active:text-dark active:no-underline ',
            className,
        )}
    >
        <>{children}</>
    </ExtendedNextLink>
);
