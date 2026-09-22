import { CollapsibleText } from 'components/Basic/CollapsibleText/CollapsibleText';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';

type CollapsibleDescriptionProps = {
    heading: string | null | undefined;
    description: string | null;
    currentPage: number;
};

export const CollapsibleDescription: FC<CollapsibleDescriptionProps> = ({ heading, description, currentPage }) => {
    return (
        <Webline>
            {!!heading && (
                <h1 className="mb-5" data-tid={TIDs.page_title}>
                    {heading}
                </h1>
            )}

            {!!description && currentPage === 1 && <CollapsibleText text={description} textClassName="max-w-5xl" />}
        </Webline>
    );
};
