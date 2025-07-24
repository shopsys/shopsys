import { ChatIcon } from 'components/Basic/Icon/ChatIcon';

export const Infobox: FC<{ message: string }> = ({ message }) => {
    return (
        <div className="bg-background-accent-less text-text-accent inline-flex w-full items-center justify-start gap-1 rounded-md px-4 py-2.5 text-sm font-semibold">
            <ChatIcon aria-hidden="true" className="text-2x size-6" />
            {message}
        </div>
    );
};
