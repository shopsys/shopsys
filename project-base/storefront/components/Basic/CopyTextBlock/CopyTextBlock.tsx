import { Button } from 'components/Forms/Button/Button';
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
        <div className="rounded bg-backgroundAccentLess">
            <div className="flex justify-end px-3 py-2">
                <Button variant="inverted" onClick={handleCopy}>
                    {copyButtonText}
                </Button>
            </div>
            <div className="p-3">
                <p>{textToCopy.length < 500 ? textToCopy : textToCopy.slice(0, 500) + '...'}</p>
            </div>
        </div>
    );
};
