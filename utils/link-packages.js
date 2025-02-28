#!/usr/bin/env node

const fs = require('fs-extra');
const path = require('path');

(async () => {
    const { globby } = await import('globby');

    try {
        const rootDir = path.join(__dirname, '..');
        const projectBaseAppDir = path.join(rootDir, 'project-base', 'app');
        const vendorShopsysDir = path.join(projectBaseAppDir, 'vendor', 'shopsys');

        const rootPkgPath = path.join(rootDir, 'package.json');
        if (!fs.existsSync(rootPkgPath)) {
            console.error('Root package.json not found');
            process.exit(1);
        }

        const rootPkg = require(rootPkgPath);
        const workspaces = rootPkg.workspaces || [];

        // Find all workspaces
        const workspacePaths = await globby(workspaces, {
            cwd: rootDir,
            onlyDirectories: true,
        });

        for (const workspaceRelPath of workspacePaths) {
            const workspaceAbsPath = path.join(rootDir, workspaceRelPath);

            if (workspaceAbsPath.startsWith(projectBaseAppDir)) {
                continue;
            }

            const packageJsonPath = path.join(workspaceAbsPath, 'package.json');

            if (!fs.existsSync(packageJsonPath)) {
                continue;
            }

            const pkgData = require(packageJsonPath);
            const packageName = pkgData.name || '';

            // Link only @shopsys/* packages
            if (packageName.startsWith('@shopsys/')) {
                const shortName = packageName.replace('@shopsys/', '');

                const packageVendorDir = path.join(vendorShopsysDir, shortName);
                const symlinkPath = path.join(packageVendorDir, 'assets');
                console.log(`Linking '${packageName}' → '${symlinkPath}'`);


                await fs.ensureDir(packageVendorDir);

                if (await fs.pathExists(symlinkPath)) {
                    await fs.remove(symlinkPath);
                }

                await fs.symlink(workspaceAbsPath, symlinkPath, 'dir');

                console.log(`Symlink created: ${symlinkPath} → ${workspaceAbsPath}`);
            }
        }
    } catch (error) {
        console.error('Error while creating symlinks:', error);
        process.exit(1);
    }
})();
