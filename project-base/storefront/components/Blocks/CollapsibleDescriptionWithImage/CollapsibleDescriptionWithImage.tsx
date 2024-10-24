import { CollapsibleText } from 'components/Basic/CollapsibleText/CollapsibleText';
import { Image } from 'components/Basic/Image/Image';

type CollapsibleDescriptionWithImageProps = {
    scrollTargetRef: React.RefObject<HTMLDivElement>;
    description: string | null;
    currentPage: number;
    imageName: string;
    imageUrl: string | undefined;
};

export const CollapsibleDescriptionWithImage: FC<CollapsibleDescriptionWithImageProps> = ({
    scrollTargetRef,
    description,
    currentPage,
    imageName,
    imageUrl,
}) => {
    return (
        <div className="mb-7 flex w-full flex-col-reverse justify-between gap-5 vl:flex-row">
            {!!description && currentPage === 1 && (
                <CollapsibleText scrollTargetRef={scrollTargetRef} text={description} />
            )}

            {imageUrl && currentPage === 1 && (
                <div className="h-full flex-shrink-0 sm:h-32">
                    <Image
                        alt={imageName}
                        className="h-full w-auto rounded-lg"
                        height={500}
                        src={imageUrl}
                        width={500}
                    />
                </div>
            )}
        </div>
    );
};
