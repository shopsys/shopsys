#!/usr/bin/env node
const fs = require('fs');
const glob = require('glob');

const getSnapshotIndexingFunction = (snapshotGroupIndex, snapshotSubgroupIndex) => {
    let snapshotGroupCounter = 0;
    return () => {
        return `${snapshotGroupIndex}-${snapshotSubgroupIndex}-${snapshotGroupCounter++}`;
    };
};

// Read and parse the SNAPSHOT_GROUP enum from the specified file
function getSnapshotGroupEnum(filePath) {
    const content = fs.readFileSync(filePath, 'utf8');
    const enumMatch = content.match(/export\s+enum\s+SNAPSHOT_GROUP\s*{([^}]+)}/);
    if (!enumMatch) {
        throw new Error(`SNAPSHOT_GROUP enum not found in file: ${filePath}`);
    }

    const enumBody = enumMatch[1]; // Extract the content inside the curly braces
    const enumObject = {};
    enumBody
        .split(',')
        .map((line) => line.trim())
        .filter((line) => line) // Remove empty lines
        .forEach((line, index) => {
            const [key, value] = line.split('=').map((part) => part.trim());
            if (value) {
                enumObject[key] = eval(value); // Evaluate string value
            } else {
                enumObject[key] = index;
            }
        });

    return enumObject;
}

// Path to the file containing the SNAPSHOT_GROUP enum
const snapshotGroupFilePath = './support/index.ts';
let SNAPSHOT_GROUP;

try {
    SNAPSHOT_GROUP = getSnapshotGroupEnum(snapshotGroupFilePath);
} catch (error) {
    console.error(error.message);
    process.exit(1);
}

const fileNames = glob.sync('./e2e/**/*.cy.ts').reverse();

// Markdown file path
const outputMarkdownFilePath = './snapshots-info-table.md';
const markdownLines = ['# Snapshots Info Lookup Table', ''];

const groupedData = {};

for (let fileName of fileNames) {
    const content = fs.readFileSync(fileName, 'utf8'); // Read the file content

    // Find the SUBGROUP_INDEX constant
    const subgroupMatch = content.match(/const\s+SUBGROUP_INDEX\s*=\s*(\d+)/);

    if (!subgroupMatch) {
        continue;
    }
    const snapshotGroupName = [...content.matchAll(/SNAPSHOT_GROUP\.(\w+)/g)]?.[0][1] ?? '';
    if (!snapshotGroupName) {
        console.error(`Snapshot group name ${snapshotGroupName} not found in file: ${fileName}`);
        continue;
    }
    const snaphsotGroupIndex = SNAPSHOT_GROUP[snapshotGroupName];
    const subgroupValue = subgroupMatch[1]; // Extract the value of SUBGROUP_INDEX
    const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(snaphsotGroupIndex, subgroupValue);

    // Initialize the group in groupedData if not already present
    if (!groupedData[snapshotGroupName]) {
        groupedData[snapshotGroupName] = [];
    }

    // Find all occurrences of it('...',)
    const testMatches = Array.from(content.matchAll(/it\(['"`](.*?)['"`],/g));

    testMatches.forEach((match, index) => {
        const testName = match[1];

        // Find all takeSnapshotAndCompare calls in this test up to the next it() block
        const testContent = content.substring(match.index, testMatches[index + 1]?.index);
        const snapshotMatches = [...testContent.matchAll(/takeSnapshotAndCompare\(\s*(.*?),\s*['"`]([^'"`]+)['"`]/gs)];
        snapshotMatches.forEach((snapshotMatch) => {
            groupedData[snapshotGroupName].push({
                snapshotId: getSnapshotFullIndexAsString(),
                testName,
                snapshotDetail: snapshotMatch[2],
                file: fileName.split('/').pop(),
            });
        });
    });
}

// Build the markdown output
Object.entries(groupedData).forEach(([group, rows]) => {
    markdownLines.push(`## Snapshot Group - ${group}`, '');
    markdownLines.push('| Snapshot Id | Test Name | Snapshot Detail | File |');
    markdownLines.push('|-------------|-----------|-----------------|------|');
    rows.forEach(({ snapshotId, testName, snapshotDetail, file }) => {
        markdownLines.push(`| ${snapshotId} | ${testName} | ${snapshotDetail} | ${file} |`);
    });
    markdownLines.push('');
});

// Write the markdown file
fs.writeFileSync(outputMarkdownFilePath, markdownLines.join('\n'));
console.log(`Markdown file written to: ${outputMarkdownFilePath}`);
