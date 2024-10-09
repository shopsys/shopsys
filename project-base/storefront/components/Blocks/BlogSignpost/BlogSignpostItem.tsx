import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { twMergeCustom } from 'utils/twMerge';

type BlogSignpostItemProps = {
    isActive: boolean;
    href: string;
    itemLevel?: number;
};

export const BlogSignpostItem: FC<BlogSignpostItemProps> = ({ children, href, isActive, itemLevel }) => (
    <ExtendedNextLink
        href={href}
        style={itemLevel !== undefined ? { marginLeft: `calc(12px*${itemLevel})` } : {}}
        type="blogCategory"
        className={twMergeCustom(
            'relative flex items-center gap-x-3 rounded-xl bg-backgroundMore px-3 py-3 font-secondary text-[14px] font-semibold leading-4 no-underline transition-all hover:no-underline',
            isActive
                ? 'bg-backgroundAccent  text-textInverted no-underline hover:bg-backgroundAccentMore hover:text-textInverted'
                : 'text-text hover:bg-backgroundMost hover:text-text',
        )}
    >
        {children}
    </ExtendedNextLink>
);
