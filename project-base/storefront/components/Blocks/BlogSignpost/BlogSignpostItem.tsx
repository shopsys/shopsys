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
            'relative flex items-center rounded py-3 pl-3 pr-9 underline hover:no-underline',
            isActive
                ? 'bg-backgroundAccent  text-textInverted no-underline hover:bg-backgroundAccentMore hover:text-textInverted'
                : 'text-text hover:text-text',
        )}
    >
        {children}
    </ExtendedNextLink>
);
