import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { VariantFragmentApi } from 'graphql/generated';
import { twMergeCustom } from 'helpers/twMerge';

type ProductDetailContentProps = {
    variants: VariantFragmentApi[];
    currentProductUuid: string;
};

const variantWrapperTwClass = 'outline outline-2 border gap-2 p-2 outline-offset-0 rounded flex flex-col items-center';

export const ProductDetailVariants: FC<ProductDetailContentProps> = ({
    variants,
    currentProductUuid: currentProduct,
}) => {
    return (
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-3 gap-2">
            {variants.map((variant) => {
                const isCurrentProduct = variant.uuid === currentProduct;
                const variantImage = variant.images.length ? variant.images[0] : undefined;
                const content = (
                    <>
                        <div className="h-24">
                            <Image
                                alt={variantImage?.name || variant.name}
                                className="object-contain w-full h-full"
                                height={120}
                                sizes="(max-width: 478px) 45vw, (max-width: 600px) 30vw, (max-width: 768px) 20vw, 15vw"
                                src={variantImage?.url}
                                width={120}
                            />
                        </div>

                        <div className="text-center text-sm">{variant.name}</div>
                    </>
                );

                if (isCurrentProduct) {
                    return (
                        <div
                            key={variant.uuid}
                            className={twMergeCustom(variantWrapperTwClass, 'outline-primaryDarker')}
                        >
                            {content}
                        </div>
                    );
                }

                return (
                    <ExtendedNextLink
                        key={variant.uuid}
                        href={variant.link}
                        type="product"
                        className={twMergeCustom(
                            variantWrapperTwClass,
                            'hover:outline-primaryLight border-border hover:no-underline no-underline',
                        )}
                    >
                        {content}
                    </ExtendedNextLink>
                );
            })}
        </div>
    );
};
