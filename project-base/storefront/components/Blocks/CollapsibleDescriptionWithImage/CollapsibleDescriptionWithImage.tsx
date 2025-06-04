import { CollapsibleText } from 'components/Basic/CollapsibleText/CollapsibleText';
import { Image } from 'components/Basic/Image/Image';
import { Webline } from 'components/Layout/Webline/Webline';
import { memo, useRef } from 'react';

type CollapsibleDescriptionWithImageProps = {
    title: string | null | undefined;
    description: string | null;
    currentPage: number;
    imageName: string;
    imageUrl: string | undefined;
};

const CollapsibleDescriptionWithImageComp: FC<CollapsibleDescriptionWithImageProps> = ({
    title,
    description,
    currentPage,
    imageName,
    imageUrl,
}) => {
    const scrollTargetRef = useRef<HTMLDivElement>(null);

    return (
        <Webline>
            {!!title && <h1 className="mb-5">{title}</h1>}

            <section className="vl:flex-row flex w-full flex-col-reverse justify-between gap-5" ref={scrollTargetRef}>
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
                            sizes="(max-width: 479px) 90vw, (max-width: 1023px) 150px, 130px"
                            src={imageUrl}
                            width={262}
                        />
                    </div>
                )}
            </section>
        </Webline>
    );
};

export const CollapsibleDescriptionWithImage = memo(CollapsibleDescriptionWithImageComp);
