import { TriangleIcon } from 'components/Basic/Icon/TriangleIcon';
// import { SkeletonModuleBanners } from 'components/Blocks/Skeleton/SkeletonModuleBanners';
import { useSliderItemsQuery } from 'graphql/requests/sliderItems/queries/SliderItemsQuery.generated';
// import dynamic from 'next/dynamic';
import Skeleton from 'react-loading-skeleton';

const BannersSliderPlaceholder: FC = () => {
    const [{ data: sliderItemsData }] = useSliderItemsQuery();

    if (!sliderItemsData?.sliderItems.length) {
        return null;
    }

    return (
        <div className="flex flex-col gap-6 vl:flex-row">
            <div className="h-[283px] rounded vl:basis-3/4">
                <div className="flex h-full w-full items-center justify-center">
                    <Skeleton className="h-full" containerClassName="h-full w-full" />
                </div>
            </div>
            <div className="flex flex-1 justify-center gap-1 vl:flex-col vl:justify-start vl:gap-4">
                {sliderItemsData.sliderItems.map((sliderItem, index) => (
                    <button
                        key={index}
                        className="group relative block h-2 w-3 cursor-pointer rounded border-none border-blueLight bg-greyLight font-bold outline-none transition active:bg-none disabled:bg-primary vl:mx-0 vl:h-auto vl:w-full vl:border-2 vl:border-solid vl:bg-blueLight vl:py-4 vl:px-8 vl:text-left vl:hover:border-blue vl:hover:bg-blue vl:disabled:border-primary vl:disabled:bg-creamWhite"
                        disabled={index === 0 % sliderItemsData.sliderItems.length}
                    >
                        <TriangleIcon className="absolute top-1/2 left-3 hidden w-2 -translate-y-1/2 text-primary vl:group-disabled:block" />
                        <span className="hidden vl:inline-block">{sliderItem.name}</span>
                    </button>
                ))}
            </div>
        </div>
    );
};

// const BannersSlider = dynamic(() => import('./BannersSlider').then((component) => component.BannersSlider), {
//     ssr: false,
//     loading: () => <BannersSliderPlaceholder />,
// });

export const Banners: FC = () => {
    // const [{ data: sliderItemsData, fetching }] = useSliderItemsQuery();

    // if (fetching) {
    //     return <SkeletonModuleBanners />;
    // }

    // if (!sliderItemsData?.sliderItems.length) {
    //     return null;
    // }

    return <BannersSliderPlaceholder />;

    // return <BannersSlider sliderItems={sliderItemsData.sliderItems} />;
};
