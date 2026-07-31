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

## Displaying the image

This section is about **administration Twig templates only**.
Use the `image()` function to render an `<img>` tag, optionally specifying `type` for a named image type:

```twig
{{ image(brand) }}
{{ image(advert, {type: 'web', height: 30}) }}
```

`imageUrl()` returns just the URL. Both are defined in [`ImageExtension`]({{github.link}}/packages/framework/src/Twig/ImageExtension.php).

On the storefront, images are fetched through the GraphQL Frontend API and rendered with the [Image component](../storefront/image-component.md), which resizes them on-the-fly via the image proxy — see that article for how sizing works there.
