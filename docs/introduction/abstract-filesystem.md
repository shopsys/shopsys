# Abstract Filesystem

One of the goals of Shopsys Platform is to give you tools to make your e-commerce platform scalable.
One of the requirements for scalable application is separated file storage that is accessible from all application instances.

We use abstract filesystem - [Flysystem](https://github.com/thephpleague/flysystem).

## Filesystem Structure

In Shopsys Platform, we use two main filesystem instances:

1. **Local Filesystem** - Used for temporary operations on the local machine. This is implemented by `LocalFilesystemFactory` which creates a filesystem with root at `/`.
2. **Main Filesystem** - The primary filesystem used for storing persistent files. By default, this is implemented by `MainFilesystemFactory` which creates a filesystem with root at the project directory.

These filesystems are registered as services in `services.yaml`:

```yaml
local_filesystem:
    class: League\Flysystem\FilesystemOperator
    factory: ['@Shopsys\FrameworkBundle\Component\Filesystem\LocalFilesystemFactory', create]

main_filesystem:
    class: League\Flysystem\FilesystemOperator
    factory: ['@Shopsys\FrameworkBundle\Component\Filesystem\MainFilesystemFactory', create]
```

## Flysystem

[Flysystem](https://github.com/thephpleague/flysystem) allows you to easily swap out a local filesystem for a remote one like Redis, Amazon S3, Dropbox etc.

### What is Flysystem used for

In Shopsys Platform we currently use [Flysystem](https://github.com/thephpleague/flysystem) to store:

- uploaded files and images
- uploaded files and images via WYSIWYG
- generated feeds
- generated sitemaps

## Using the Filesystem in Your Code

To use the filesystem in your code, you can inject the `FilesystemOperator` service:

```php
use League\Flysystem\FilesystemOperator;

class YourService
{
    public function __construct(
        private readonly FilesystemOperator $filesystem
    ) {
    }

    public function saveFile(string $content, string $path): void
    {
        $this->filesystem->write($path, $content);
    }

    public function readFile(string $path): string
    {
        return $this->filesystem->read($path);
    }

    public function fileExists(string $path): bool
    {
        return $this->filesystem->has($path);
    }

    public function deleteFile(string $path): void
    {
        if ($this->filesystem->has($path)) {
            $this->filesystem->delete($path);
        }
    }
}
```

### Common Filesystem Operations

Here are some common operations you can perform with the filesystem:

- **Check if a file exists**: `$filesystem->has($path)`
- **Read a file**: `$filesystem->read($path)`
- **Write to a file**: `$filesystem->write($path, $content)`
- **Delete a file**: `$filesystem->delete($path)`
- **Create a directory**: `$filesystem->createDirectory($path)`
- **List contents of a directory**: `$filesystem->listContents($path)`
- **Get file metadata**: `$filesystem->lastModified($path)`, `$filesystem->fileSize($path)`, `$filesystem->mimeType($path)`

### How to change storage adapter for filesystem

Flysystem supports a huge number of storage adapters. You can find [full list here](https://github.com/thephpleague/flysystem#community-integrations).

If you want to change the adapter used for Filesystem you must implement factory for `FilesystemFactoryInterface` and register it in `services.yaml` file under `main_filesystem` alias.

For example, to use Amazon S3 as your main filesystem:

```php
namespace App\Component\Filesystem;

use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\Filesystem\FilesystemFactoryInterface;
use Aws\S3\S3Client;

class S3FilesystemFactory implements FilesystemFactoryInterface
{
    public function __construct(
        private readonly string $bucket,
        private readonly string $region,
        private readonly string $key,
        private readonly string $secret
    ) {
    }

    public function create(): FilesystemOperator
    {
        $client = new S3Client([
            'credentials' => [
                'key' => $this->key,
                'secret' => $this->secret,
            ],
            'region' => $this->region,
            'version' => 'latest',
        ]);

        $adapter = new AwsS3V3Adapter($client, $this->bucket);

        return new Filesystem($adapter);
    }
}
```

Then register it in your `services.yaml`:

```yaml
App\Component\Filesystem\S3FilesystemFactory:
    arguments:
        $bucket: '%env(S3_BUCKET)%'
        $region: '%env(S3_REGION)%'
        $key: '%env(S3_KEY)%'
        $secret: '%env(S3_SECRET)%'

main_filesystem:
    class: League\Flysystem\FilesystemOperator
    factory: ['@App\Component\Filesystem\S3FilesystemFactory', create]
```

#### How to change storage adapter for WYSIWYG

WYSIWYG configuration is stored in `config/packages/fm_elfinder.yaml` file in `fm_elfinder\instances\default\connector\roots` section.
For more information how to set up Flysystem with WYSIWYG visit [FMElfinderBundle Documentation](https://github.com/helios-ag/FMElfinderBundle/blob/9.2/docs/flysystem.md).

#### Create Nginx proxy to load files from different storage

If you changed the file storage, you have to change also loading of these files to be accessible from the frontend of your application.
You need to update your Nginx proxy to access your new storage.

For instance, you can take a look of implementation for S3 Storage in [Shopsys Kubernetes Deployment package](https://github.com/shopsys/deployment/blob/v1.1.0/kubernetes/configmap/nginx.yaml#L146)

## The Inevitable Exceptions

In some cases, you need to download/upload files to your local filesystem, do some job with them and then upload the result via the abstract filesystem. This is common for operations like:

- Processing large files that need to be manipulated locally before storing
- Working with external libraries that only support local files
- Generating files that need to be processed before storing

### Example: Working with Local and Abstract Filesystems

Here's an example of how to work with both local and abstract filesystems, based on the `FeedExport` class:

```php
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Symfony\Component\Filesystem\Filesystem;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;

class FileProcessor
{
    public function __construct(
        private readonly FilesystemOperator $filesystem,
        private readonly Filesystem $localFilesystem,
        private readonly MountManager $mountManager,
        private readonly TransformStringHelper $transformStringHelper
    ) {
    }

    public function processFile(string $sourcePath, string $destinationPath): void
    {
        // Define temporary local path
        $tempLocalPath = sys_get_temp_dir() . '/temp_file';

        // Check if source file exists in abstract filesystem
        if ($this->filesystem->has($sourcePath)) {
            // Move file from abstract to local filesystem
            $this->mountManager->move(
                'main://' . $sourcePath,
                'local://' . $this->transformStringHelper->removeDriveLetterFromPath($tempLocalPath)
            );
        } else {
            // Create empty file if source doesn't exist
            $this->localFilesystem->touch($tempLocalPath);
        }

        // Process the file locally
        // ... your processing code here ...

        // Move processed file back to abstract filesystem
        $this->mountManager->move(
            'local://' . $this->transformStringHelper->removeDriveLetterFromPath($tempLocalPath),
            'main://' . $destinationPath
        );
    }
}
```

## Troubleshooting

### Windows Filesystem Issues

When working with Windows-like filesystems (`C:`, `D:`, etc.), you may encounter issues with file operations. This is because Flysystem doesn't handle drive letters well. To solve this, use the `TransformStringHelper::removeDriveLetterFromPath` method to remove the drive letter from the path:

```php
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;

$path = 'C:\path\to\file.txt';
$pathWithoutDriveLetter = $transformStringHelper->removeDriveLetterFromPath($path);
// Result: '\path\to\file.txt'
```

### Permission Issues

If you encounter permission issues when working with files, make sure that:

1. The web server user has write permissions to the directories where files are stored
2. When using remote storage like S3, the credentials have the necessary permissions to read/write files

### File Not Found Issues

If you're getting "file not found" errors:

1. Check if you're using the correct filesystem instance (local vs. main)
2. Verify the path is correct and doesn't include unnecessary leading slashes
3. Make sure the file actually exists in the expected location

### Moving Files Between Filesystems

When moving files between different filesystems, always use the `MountManager` instead of trying to read and write the files manually. This is more efficient and handles edge cases better:

```php
// Instead of:
$content = $sourceFilesystem->read($sourcePath);
$destinationFilesystem->write($destinationPath, $content);

// Use:
$mountManager->move(
    'source://' . $sourcePath,
    'destination://' . $destinationPath
);
```
