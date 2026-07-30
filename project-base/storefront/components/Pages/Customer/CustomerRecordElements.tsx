import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { Tooltip } from 'components/Basic/Tooltip/Tooltip';
import { ReactNode } from 'react';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';

type CustomerRecordCardProps = {
    children: ReactNode;
    className?: string;
};

export const CustomerRecordCard: FC<CustomerRecordCardProps> = ({ children, className }) => (
    <div
        className={twMergeCustom(
            'flex vl:flex-row flex-col flex-wrap justify-between gap-4 rounded-xl bg-background-more p-5',
            className,
        )}
    >
        {children}
    </div>
);

type CustomerRecordElementWithImageProps = {
    image: string | undefined;
    name: ReactNode;
    imageAlt?: string;
    tid?: string;
};

export const CustomerRecordElementWithImage: FC<CustomerRecordElementWithImageProps> = ({
    image,
    name,
    imageAlt,
    tid,
}) => (
    <div className="flex items-center gap-4 font-secondary font-semibold">
        <div
            className="flex h-12 w-20 shrink-0 items-center justify-center rounded-xl bg-background-default"
            data-tid={tid}
        >
            <Image
                alt={imageAlt ?? (typeof name === 'string' ? name : '')}
                className="aspect-video h-7 object-contain object-center mix-blend-multiply"
                height={28}
                src={image}
                width={60}
            />
        </div>

        {name}
    </div>
);

type CustomerRecordColumnInfoProps = {
    title: string;
    children: ReactNode;
    className?: string;
    valueClassName?: string;
    tid?: string;
};

export const CustomerRecordColumnInfo: FC<CustomerRecordColumnInfoProps> = ({
    title,
    children,
    className,
    valueClassName,
    tid,
}) => (
    <div className={twMergeCustom('flex min-w-25 flex-col gap-1 font-secondary font-semibold text-sm', className)}>
        <span className="text-text-less">{title}</span>
        <span className={valueClassName} data-tid={tid}>
            {children}
        </span>
    </div>
);

type CustomerRecordRowInfoProps = {
    title: string;
    children: ReactNode;
    className?: string;
    titleClassName?: string;
    tid?: string;
};

export const CustomerRecordRowInfo: FC<CustomerRecordRowInfoProps> = ({
    title,
    children,
    className,
    titleClassName,
    tid,
}) => (
    <div
        className={twMergeCustom('flex vl:flex-row flex-col vl:items-center gap-1 vl:gap-3 text-sm', className)}
        data-tid={tid}
    >
        <span className={twMergeCustom('min-w-25 font-secondary font-semibold text-text-less', titleClassName)}>
            {title}
        </span>
        {children}
    </div>
);

type CustomerRecordProductImageProps = {
    image: string | undefined;
    imageAlt: string;
    isVisible?: boolean;
    link?: string;
    quantity?: number;
    tid?: string;
    tooltipLabel?: string;
};

export const CustomerRecordProductImage: FC<CustomerRecordProductImageProps> = ({
    image,
    imageAlt,
    isVisible,
    link,
    quantity = 1,
    tid,
    tooltipLabel,
}) => {
    const imageElement = (
        <Image
            alt={imageAlt}
            className="size-12 object-contain mix-blend-multiply"
            height={48}
            src={image}
            width={48}
        />
    );

    const productImage = (
        <div
            className={twJoin(
                'relative size-16 shrink-0 rounded-xl border border-transparent bg-base-white p-2 transition-all',
                isVisible && 'hover:border-border-less',
            )}
            data-tid={tid}
        >
            {isVisible && link ? (
                <ExtendedNextLink href={link} type="product">
                    {imageElement}
                </ExtendedNextLink>
            ) : (
                imageElement
            )}

            {quantity > 1 && (
                <span className="absolute -top-2 -right-2 flex h-5 min-w-5 items-center justify-center rounded-full bg-icon-accent-brand-less px-0.5 font-semibold text-text-inverted text-xs">
                    {quantity}
                </span>
            )}
        </div>
    );

    if (tooltipLabel) {
        return <Tooltip label={tooltipLabel}>{productImage}</Tooltip>;
    }

    return productImage;
};
