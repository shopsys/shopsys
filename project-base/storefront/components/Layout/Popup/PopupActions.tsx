import { twMergeCustom } from 'utils/twMerge';

/**
 * The actions at the end of a popup: aligned right with the secondary action first and the main action last,
 * stacked on mobile. Give the buttons `className="w-full sm:w-auto"` so they take the full width there.
 */
export const PopupActions: FC = ({ children, className }) => {
    return <div className={twMergeCustom('flex flex-col justify-end gap-3 sm:flex-row', className)}>{children}</div>;
};
