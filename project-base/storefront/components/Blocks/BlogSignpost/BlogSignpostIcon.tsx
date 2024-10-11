import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { twMergeCustom } from 'utils/twMerge';

type BlogSignpostIconProps = { isActive: boolean };

export const BlogSignpostIcon: FC<BlogSignpostIconProps> = ({ isActive }) => (
    <ArrowIcon
        className={twMergeCustom(
            'w-[18px] -rotate-90 p-[3.5px] text-textSubtle',
            isActive ? 'text-textInverted' : 'text-text',
        )}
    />
);
