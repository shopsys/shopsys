export type TypeContentBlock<TData = Record<string, unknown>> = {
    id: string;
    type: string;
    data: TData;
    children?: TypeContentBlock[][];
};

export type TypeContentDocument = {
    version: number;
    profile: string;
    blocks: TypeContentBlock[];
};

export type BlockPosition = 'left' | 'center' | 'right';

export type TextBlockData = { html: string };

// anchorId is not part of the document, addHeadingAnchors adds it to the headings of the table of contents
export type HeadingBlockData = { text: string; level: 'h2' | 'h3' | 'h4'; anchorId?: string };

export type ImageBlockData = {
    url: string | null;
    width?: number;
    height?: number;
    alt: string;
    position: BlockPosition;
    link: string | null;
    legacy?: boolean;
};

export type ButtonLinkBlockData = {
    text: string;
    url: string;
    openInNewTab: boolean;
    position: BlockPosition;
    variant: 'primary' | 'secondary' | 'tertiary';
};

export type VideoBlockData = {
    provider: string | null;
    videoId: string | null;
    embedUrl: string | null;
};

export type MapBlockData = { url: string };

export type IframeBlockData = { url: string; height: number };

export type ProductsBlockData = { catnums: string[] };

export type ColumnsBlockData = { layout: '1/2-1/2' | '1/3-2/3' | '2/3-1/3' | '1/3-1/3-1/3' };
