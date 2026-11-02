export const getSkipSnapshots = (value: string | undefined): boolean => {
    if (value !== undefined && value !== '0' && value !== '1') {
        throw new Error('SKIP_SNAPSHOTS must be 0 or 1.');
    }

    return value === '1';
};

export const validateSnapshotMode = (skipSnapshots: boolean, visualRegressionType: string | undefined): void => {
    if (skipSnapshots && visualRegressionType === 'base') {
        throw new Error('Cannot generate screenshot references with skipSnapshots enabled.');
    }
};
