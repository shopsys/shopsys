export const createSnapshotIndexer = (
    snapshotGroupIndex: number,
    snapshotSubgroupIndex: number,
    getTestContext: () => { titlePath: string[]; retry: number },
) => {
    let snapshotCounter = 0;
    let counterAtTestStart = 0;
    let lastTestPath = '';
    let lastRetryAttempt = 0;

    return (snapshotIndex?: number) => {
        // Explicit IDs survive removing other captures and running an individual test.
        if (snapshotIndex !== undefined) {
            return `${snapshotGroupIndex}-${snapshotSubgroupIndex}-${snapshotIndex}`;
        }

        const { titlePath, retry } = getTestContext();
        // Identical test titles in sibling suites must have separate retry counters.
        const testPath = JSON.stringify(titlePath);
        if (testPath !== lastTestPath) {
            lastTestPath = testPath;
            counterAtTestStart = snapshotCounter;
            lastRetryAttempt = retry;
        } else if (retry > lastRetryAttempt) {
            snapshotCounter = counterAtTestStart;
            lastRetryAttempt = retry;
        }

        return `${snapshotGroupIndex}-${snapshotSubgroupIndex}-${snapshotCounter++}`;
    };
};
