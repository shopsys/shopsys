import { compressToEncodedURIComponent, decompressFromEncodedURIComponent } from 'lz-string';

export const compressObjectToString = (object: Record<string, unknown>): string =>
    compressToEncodedURIComponent(JSON.stringify(object));

export const decompressStringToObject = <T>(string: string | undefined): T | undefined => {
    if (!string) {
        return undefined;
    }

    const decompressedString = decompressFromEncodedURIComponent(string);

    return JSON.parse(decompressedString);
};
