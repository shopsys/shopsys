export const getImageAlt = (imageName: string | null | undefined, fallback: string): string =>
    imageName?.trim() || fallback.trim();
