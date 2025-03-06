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
        <div className="vl:flex-row flex w-full flex-col-reverse justify-between gap-5">
            {!!description && currentPage === 1 && (
                <CollapsibleText scrollTargetRef={scrollTargetRef} text={description} />
            )}

            {imageUrl && currentPage === 1 && (
                <div className="h-full shrink-0 sm:h-32">
                    <Image
                        priority
                        alt={imageName}
                        className="vl:size-[130px] h-[262px] w-full rounded-lg object-contain sm:h-[130px] sm:w-fit"
                        height={262}
                        sizes="50vw"
                        src={imageUrl}
                        width={262}
                    />
                </div>
            )}
        </div>
    );
};
