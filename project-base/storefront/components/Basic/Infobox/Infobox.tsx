import { ChatIcon } from 'components/Basic/Icon/ChatIcon';

export const Infobox: FC<{ message: string }> = ({ message }) => {
    return (
        <div className="inline-flex w-full items-center justify-start gap-1 rounded-md bg-background-accent-less px-4 py-2.5 font-semibold text-sm text-text-accent">
            <ChatIcon aria-hidden="true" className="size-6 text-2x" />
            {message}
        </div>
    );
};
