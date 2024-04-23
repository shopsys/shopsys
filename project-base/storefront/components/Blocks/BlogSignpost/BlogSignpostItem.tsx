import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { twJoin } from 'tailwind-merge';

type BlogSignpostItemProps = {
    isActive: boolean;
    href: string;
    itemLevel?: number;
};

export const BlogSignpostItem: FC<BlogSignpostItemProps> = ({ children, href, isActive, itemLevel }) => (
    <ExtendedNextLink
        href={href}
        style={itemLevel !== undefined ? { marginLeft: `calc(6px*${itemLevel})` } : {}}
        type="blogCategory"
        className={twJoin(
            'relative flex items-center rounded py-3 pr-9 pl-3 underline hover:no-underline',
            isActive ? 'bg-whiteSnow  text-dark no-underline hover:text-dark' : 'text-whiteSnow hover:text-whiteSnow',
            itemLevel !== undefined && '',
        )}
    >
        {children}
    </ExtendedNextLink>
);
