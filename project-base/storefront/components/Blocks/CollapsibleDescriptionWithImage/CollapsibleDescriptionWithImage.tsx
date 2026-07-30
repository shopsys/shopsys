import { CollapsibleText } from 'components/Basic/CollapsibleText/CollapsibleText';
import { Image } from 'components/Basic/Image/Image';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { useRef } from 'react';

type CollapsibleDescriptionWithImageProps = {
    title: string | null | undefined;
    description: string | null;
    currentPage: number;
    imageName: string;
    imageUrl: string | undefined;
    textClassName?: string;
};

export const CollapsibleDescriptionWithImage: FC<CollapsibleDescriptionWithImageProps> = ({
    title,
    description,
    currentPage,
    imageName,
    imageUrl,
    textClassName,
}) => {
    const scrollTargetRef = useRef<HTMLDivElement>(null);

    return (
        <Webline>
            {!!title && (
                <h1 className="mb-5" data-tid={TIDs.page_title}>
                    {title}
                </h1>
            )}

            <section
                className="flex w-full scroll-mt-fixed-header vl:flex-row flex-col-reverse justify-between gap-5"
                ref={scrollTargetRef}
            >
                {!!description && currentPage === 1 && (
                    <CollapsibleText
                        scrollTargetRef={scrollTargetRef}
                        text={description}
                        textClassName={textClassName}
                    />
                )}

                {imageUrl && currentPage === 1 && (
                    <div className="h-full shrink-0 sm:h-32">
                        <Image
                            priority
                            alt={imageName}
                            className="vl:size-32.5 h-65.5 w-full rounded-lg object-contain sm:h-32.5 sm:w-fit"
                            height={262}
                            sizes="(max-width: 479px) 90vw, 130px"
                            src={imageUrl}
                            width={262}
                        />
                    </div>
                )}
            </section>
        </Webline>
    );
};
