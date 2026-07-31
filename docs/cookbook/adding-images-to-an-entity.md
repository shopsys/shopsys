# Adding Images to an Entity

Add the [`EntityImage`]({{github.link}}/packages/framework/src/Component/Image/Config/Attributes/EntityImage.php) attribute to an entity class to let it hold uploaded images.
No marker interface or manual registration is needed — see [Registration](#registration) below.

```php
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;

#[ORM\Entity]
#[EntityImage]
class Brand extends AbstractTranslatableEntity
{
    // ...
}
```

## Options

#### multiple

Set to `true` to allow a gallery of images per entity instance instead of a single image, e.g. `#[EntityImage(multiple: true)]` on `Product`.

#### name

The attribute is repeatable, so an entity can declare several named image types (e.g. a desktop and a mobile variant):

```php
#[EntityImage]
#[EntityImage('web')]
#[EntityImage('mobile')]
class SliderItem implements OrderableEntityInterface
```

#### Folder name

By default, images are stored in a folder derived from the entity's class name.
Override it with [`EntityImageFolder`]({{github.link}}/packages/framework/src/Component/Image/Config/Attributes/EntityImageFolder.php):

```php
#[EntityImageFolder('noticer')]
#[EntityImage]
class Advert
{
    // ...
}
```

## Registration

Entities are not listed in any config file — [`RegisterImageEntitiesCompilerPass`]({{github.link}}/packages/framework/src/DependencyInjection/Compiler/RegisterImageEntitiesCompilerPass.php) scans all Doctrine entities for the `EntityImage` attribute at container compile time.

## Uploading the image in a form

Add an [`ImageUploadType`]({{github.link}}/packages/framework/src/Form/ImageUploadType.php) field to the entity's form type:

```php
$builder->add('image', ImageUploadType::class, [
    'image_entity_class' => Brand::class,
    'entity' => $brand, // null when creating a new entity
    'required' => false,
]);
```

Pass `image_type` when the entity declares a named image type.
See [Using Form Types → ImageUploadType](../introduction/using-form-types.md#imageuploadtype) for the full list of options.

## Saving the uploaded image

The form field alone only lets an administrator pick a file — persisting it is a separate step.
In the entity's facade, call [`ImageFacade::manageImages()`]({{github.link}}/packages/framework/src/Component/Image/ImageFacade.php) after the entity is persisted, passing the `ImageUploadData` collected by the form:

```php
$this->em->persist($brand);
$this->em->flush();
$this->imageFacade->manageImages($brand, $brandData->image);
```

`manageImages()` needs the entity to already have an ID, so call it after `persist()`/`flush()` (on create) or after `flush()` (on edit).
For a named image type, pass its `name` as the third argument — matching the `image_type` used in the form field — and repeat the call once per type:

```php
$this->imageFacade->manageImages($sliderItem, $sliderItemData->image, self::IMAGE_TYPE_WEB);
$this->imageFacade->manageImages($sliderItem, $sliderItemData->mobileImage, self::IMAGE_TYPE_MOBILE);
```

## Attaching images outside the administration

When images come from an ERP transfer or a migration from another platform, there is no form to build the `ImageUploadData`. Create it yourself and call the same `ImageFacade::manageImages()`.

`ImageUploadData::$uploadedFiles` holds names of temporary files that must already exist in the upload directory of the [abstract filesystem](../introduction/abstract-filesystem.md), so copy the file there first using [`FileUpload`]({{github.link}}/packages/framework/src/Component/FileUpload/FileUpload.php) and the Flysystem `MountManager`:

```php
$temporaryFilename = $this->fileUpload->getTemporaryFilename('logo.png');
$this->mountManager->copy(
    'local://' . $localPath,
    'main://' . $this->fileUpload->getTemporaryFilepath($temporaryFilename),
);
```

When the source is a remote URL, no local file is needed, you can write the downloaded content straight to the temporary directory:

```php
$content = $this->httpClient->request('GET', $imageUrl)->getContent();
$this->mountManager->write(
    'main://' . $this->fileUpload->getTemporaryFilepath($temporaryFilename),
    $content,
);
```

The image format is recognized from the filename extension (`jpg`, `jpeg`, `png`, `gif`, or `svg` are supported), so make sure the temporary filename has one.

Then build the `ImageUploadData` using [`ImageUploadDataFactory::createFromEntityAndType()`]({{github.link}}/packages/framework/src/Component/FileUpload/ImageUploadDataFactory.php) — unlike a plain `new ImageUploadData()`, it pre-fills the entity's existing images, so replacing the image of a non-`multiple` type works correctly:

```php
$imageUploadData = $this->imageUploadDataFactory->createFromEntityAndType($brand);
$imageUploadData->uploadedFiles[] = $temporaryFilename;
$imageUploadData->uploadedFilenames[] = ['en' => 'Brand logo'];
$this->imageFacade->manageImages($brand, $imageUploadData);
```

A few things to keep in mind in an import:

- The entity must already have an ID, just like in the form scenario above.
- `manageImages()` flushes the entity manager internally — mind that when batching with `EntityManager::clear()`.
- The temporary file is consumed (converted and removed) during the upload, so an `ImageUploadData` instance cannot be reused for a second entity.
- For a named image type, pass its name as the third argument of `manageImages()`; for the default type, pass `null`.
- A recurring transfer needs its own change detection — `manageImages()` cannot tell an already-imported image from a new one, so each run of the transfer would add the same images again.
  Extend the `Image` entity with an identifier from the external system (e.g. a file hash) and compare it against the incoming data to decide which images to add, keep, or put into `$imagesToDelete`.

For the overall structure of an import (cron module, external IDs, batching), see [Basic Data Import](basic-data-import.md).

The demo [`ImageDataFixture`]({{github.link}}/project-base/app/src/DataFixtures/Demo/ImageDataFixture.php) is usually **not** an example to follow — it inserts image database rows with raw SQL and hard-coded IDs as a performance shortcut for demo data.

## Displaying the image

This section is about **administration Twig templates only**.
Use the `image()` function to render an `<img>` tag, optionally specifying `type` for a named image type:

```twig
{{ image(brand) }}
{{ image(advert, {type: 'web', height: 30}) }}
```

`imageUrl()` returns just the URL. Both are defined in [`ImageExtension`]({{github.link}}/packages/framework/src/Twig/ImageExtension.php).

On the storefront, images are fetched through the GraphQL Frontend API and rendered with the [Image component](../storefront/image-component.md), which resizes them on-the-fly via the image proxy — see that article for how sizing works there.
