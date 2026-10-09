export const createSnapshotIndexer = (
    snapshotGroupIndex: number,
    snapshotSubgroupIndex: number,
    getTestContext: () => { title: string; retry: number },
) => {
    let snapshotCounter = 0;
    let counterAtTestStart = 0;
    let lastTestTitle = '';
    let lastRetryAttempt = 0;

    return (snapshotIndex?: number) => {
        // Explicit IDs survive removing other captures and running an individual test.
        if (snapshotIndex !== undefined) {
            return `${snapshotGroupIndex}-${snapshotSubgroupIndex}-${snapshotIndex}`;
        }

        const { title, retry } = getTestContext();
        if (title !== lastTestTitle) {
            lastTestTitle = title;
            counterAtTestStart = snapshotCounter;
            lastRetryAttempt = 0;
        } else if (retry > lastRetryAttempt) {
            snapshotCounter = counterAtTestStart;
            lastRetryAttempt = retry;
        }

        return `${snapshotGroupIndex}-${snapshotSubgroupIndex}-${snapshotCounter++}`;
    };
};
