import { SyntheticEvent, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { twMergeCustom } from 'utils/twMerge';

const notImplementedMessage = 'Not implemented yet';

export const notImplementedYetHandler = (e: SyntheticEvent): void => {
    showErrorMessage(notImplementedMessage);
    e.preventDefault();
};

const notImplementedTagTwClass =
    'whitespace-nowrap rounded-sm bg-red p-1 text-center text-xs font-normal text-white opacity-100';
const notImplementedTagPositionedTwClass =
    'whitespace-nowrap rounded-sm bg-red p-1 text-center text-xs font-normal text-white opacity-100 absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2';
const notImplementedBorderTwClass = 'border border-dashed border-red';

export const NotImplementedYetWrapper: FC = ({ children }) => {
    return (
        <div className={twJoin('relative', notImplementedBorderTwClass)}>
            {children}
            <div className={notImplementedTagPositionedTwClass}>{notImplementedMessage}</div>
        </div>
    );
};

export const NotImplementedYetInject: FC = () => {
    return (
        <div
            className={twJoin(
                'pointer-events-none absolute bottom-0 left-0 right-0 top-0',
                notImplementedBorderTwClass,
            )}
        >
            <div className={twMergeCustom(notImplementedTagPositionedTwClass, 'top-0 -translate-y-0')}>
                {notImplementedMessage}
            </div>
        </div>
    );
};

export const NotImplementedYetTag: FC = () => {
    return (
        <div
            className={twJoin('pointer-events-none ml-2 inline-block align-bottom leading-3', notImplementedTagTwClass)}
        >
            {notImplementedMessage}
        </div>
    );
};

export const NotImplementedTooltip: FC = ({ children, className }) => {
    const [active, setActive] = useState(false);

    const toggleState = () => setActive(!active);

    return (
        <div
            className={twMergeCustom('relative', notImplementedBorderTwClass, className)}
            onMouseEnter={toggleState}
            onMouseLeave={toggleState}
        >
            {children}
            {active && (
                <div
                    className={twJoin(
                        'absolute -bottom-8 left-1/2 z-tooltip h-6 -translate-x-1/2 leading-4',
                        notImplementedTagTwClass,
                    )}
                >
                    {notImplementedMessage}
                </div>
            )}
        </div>
    );
};
