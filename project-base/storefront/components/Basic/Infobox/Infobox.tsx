import { ChatIcon } from 'components/Basic/Icon/ChatIcon';

export const Infobox: FC<{ message: string }> = ({ message }) => {
    return (
        <div className="inline-flex w-full items-center justify-start gap-1 rounded-md bg-backgroundAccentLess px-4 py-2.5 text-sm font-semibold text-textAccent">
            <ChatIcon className="text-2x size-6" />
            {message}
        </div>
    );
};
