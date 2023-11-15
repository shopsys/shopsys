export const createEmptyArray = (length: number): null[] => Array(length).fill(null);

export const mergeNullableArrays = <T>(array1: T[] | undefined | null, array2: T[] | undefined | null) => {
    const nonNullableArray1 = array1 ? array1 : [];
    const nonNullableArray2 = array2 ? array2 : [];

    return [...nonNullableArray1, ...nonNullableArray2];
};
