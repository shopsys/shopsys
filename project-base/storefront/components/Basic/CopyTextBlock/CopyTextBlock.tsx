import { useState } from 'react';

type CopyTextBlockProps = {
    textToCopy: string;
};

export const CopyTextBlock: FC<CopyTextBlockProps> = ({ textToCopy }) => {
    const [copyButtonText, setCopyButtonText] = useState('Copy Text');

    const handleCopy = async () => {
        try {
            await navigator.clipboard.writeText(textToCopy);
            setCopyButtonText('Copied');
        } catch (err) {
            setCopyButtonText('Failed while copying');
        }
    };

    return (
        <div className="rounded bg-greyLighter">
            <div className="flex justify-end rounded bg-greyDark px-3 py-2">
                <button className="text-white" onClick={handleCopy}>
                    {copyButtonText}
                </button>
            </div>
            <div className="p-3">
                <p>{textToCopy.length < 500 ? textToCopy : textToCopy.slice(0, 500) + '...'}</p>
            </div>
        </div>
    );
};

export default CopyTextBlock;
