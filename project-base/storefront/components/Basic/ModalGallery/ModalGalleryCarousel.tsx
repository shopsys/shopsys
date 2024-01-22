import { PlayIcon } from 'components/Basic/Icon/IconsSvg';
import { Image } from 'components/Basic/Image/Image';
import { VideoTokenFragmentApi, ImageFragmentApi } from 'graphql/generated';
import { RefObject } from 'react';
import { twJoin } from 'tailwind-merge';

type ModalGalleryCarouselProps = {
    items: (VideoTokenFragmentApi | ImageFragmentApi)[];
    itemsRefs: RefObject<HTMLLIElement>[];
    selectedIndex: number;
    galleryName: string;
    onSelectItem: (index: number) => void;
};

export const ModalGalleryCarousel: FC<ModalGalleryCarouselProps> = ({
    items,
    itemsRefs,
    selectedIndex,
    galleryName,
    onSelectItem,
}) => {
    return (
        <ul className="grid snap-x snap-mandatory auto-cols-[80px] grid-flow-col overflow-x-auto overscroll-x-contain [-ms-overflow-style:'none'] [scrollbar-width:'none'] [&::-webkit-scrollbar]:hidden">
            {items.map((galleryItem, index) => {
                const isImage = galleryItem.__typename === 'Image';
                const isVideo = galleryItem.__typename === 'VideoToken';

                return (
                    <li
                        key={index}
                        ref={itemsRefs[index]}
                        className={twJoin(
                            'flex h-20 w-20 snap-center items-center justify-center px-1 transition-opacity hover:opacity-100',
                            index !== selectedIndex && 'cursor-pointer opacity-40',
                        )}
                        onClick={(e) => {
                            e.stopPropagation();
                            onSelectItem(index);
                        }}
                    >
                        {isImage && (
                            <Image
                                alt={galleryItem.name || `${galleryName}-${index}`}
                                className="max-h-full w-auto object-contain"
                                draggable={false}
                                height={80}
                                src={galleryItem.url}
                                width={80}
                            />
                        )}

                        {isVideo && (
                            <div className="relative">
                                <Image
                                    alt={galleryItem.description}
                                    className="max-h-20 w-auto"
                                    draggable={false}
                                    height={80}
                                    src={`https://img.youtube.com/vi/${galleryItem.token}/1.jpg`}
                                    width={80}
                                />

                                <PlayIcon className="absolute top-1/2 left-1/2 flex h-8 w-8 -translate-y-1/2 -translate-x-1/2 items-center justify-center rounded-full bg-dark bg-opacity-50 text-white" />
                            </div>
                        )}
                    </li>
                );
            })}
        </ul>
    );
};
