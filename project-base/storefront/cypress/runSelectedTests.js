#!/usr/bin/env node

const glob = require('glob');
const inquirer = require('inquirer');
const { spawn } = require('child_process');

const files = glob.sync('./e2e/**/*.cy.ts').reverse();

const prompt = files.map((file) => ({
    type: 'input',
    name: file,
    message: `${file}:`,
    default: 'n',
}));

// Support both inquirer ESM default export and CJS usage
const promptFn = (inquirer.default?.prompt ?? inquirer.prompt).bind(inquirer.default ?? inquirer);

promptFn(prompt).then((answers) => {
    const filesToRun = Object.entries(answers)
        .filter(([_, fileObject]) => fileObject.cy.ts.toLowerCase() === 'y')
        .map(([filePath]) => `./${filePath}.cy.ts`);

    if (filesToRun.length === 0) {
        console.log('No files selected to run.');
        return;
    }

    const args = process.argv.slice(2);
    const typeArg = args.find((arg) => arg.startsWith('visualRegressionType='));
    let typeValue = 'regression';
    if (typeArg) {
        typeValue = typeArg.split('=')[1];
    } else {
        console.log('No TYPE variable specified. Using "regression"');
    }

    const command = `cypress run --env visualRegressionType=${typeValue} --spec ${filesToRun.join(',')}`;
    console.log(`Running command: ${command}`);

    const child = spawn(command, { shell: true });

    child.stdout.on('data', (data) => {
        console.log(`${data}`);
    });

    child.stderr.on('data', (data) => {
        console.error(`${data}`);
    });

    child.on('close', (code) => {
        if (code !== 0) {
            console.error(`Process exited with code ${code}`);
        } else {
            console.log('Process completed successfully.');
        }
    });
});
