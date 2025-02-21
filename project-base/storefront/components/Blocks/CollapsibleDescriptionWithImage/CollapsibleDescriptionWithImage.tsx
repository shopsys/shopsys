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
        <div className="vl:flex-row mb-7 flex w-full flex-col-reverse justify-between gap-5">
            {!!description && currentPage === 1 && (
                <CollapsibleText scrollTargetRef={scrollTargetRef} text={description} />
            )}

            {imageUrl && currentPage === 1 && (
                <div className="h-full shrink-0 sm:h-32">
                    <Image
                        alt={imageName}
                        className="h-auto w-full rounded-lg sm:h-full sm:w-auto"
                        height={500}
                        src={imageUrl}
                        width={500}
                    />
                </div>
            )}
        </div>
    );
};
