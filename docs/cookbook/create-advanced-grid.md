# Create Advanced Grid
This article provides step by step instructions for advanced grid configurations.
After finishing this cookbook, you will know how to create a grid with inline editing, and drag&drop sorting.

## Prerequisites
- you have created a grid for Salesman entity following [Create Basic Grid](./create-basic-grid.md) cookbook
- you are aware of Shopsys Framework model concepts like entity data classes and their factories, facades etc.
    - we recommend you to read the [Basics about model architecture](../model/introduction-to-model-architecture.md) article.
- a basic knowledge of [Symfony forms](https://symfony.com/doc/3.4/forms.html) might be helpful for you

## 1. Allow inline editing
In this step, we will allow creating and editing of salesmen entities (that we worked with in the [previous cookbook](./create-basic-grid.md)) directly using the grid.
As a preparation for that, we need to implement the creation and editing logic, first.

### 1.1 Create `SalesmanData` class
```php
// src/Model/Salesman/SalesmanData.php

declare(strict_types=1);

namespace App\Model\Salesman;

class SalesmanData
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var \DateTime|null
     *
     */
    public $registeredAt;
}
```

### 1.2 Add constructor, `edit` method, and getters to `Salesman` entity
```diff
// src/Model/Salesman/Salesman.php

class Salesman
{
+     /**
+      * @param \App\Model\Salesman\SalesmanData $salesmanData
+      */
+     public function __construct(SalesmanData $salesmanData)
+     {
+         $this->edit($salesmanData);
+     }

+     /**
+      * @param \App\Model\Salesman\SalesmanData $salesmanData
+      */
+     public function edit(SalesmanData $salesmanData)
+     {
+         $this->name = $salesmanData->name;
+         $this->registeredAt = $salesmanData->registeredAt;
+     }

+    /**
+     * @return int
+     */
+    public function getId(): int
+    {
+        return $this->id;
+    }

+    /**
+     * @return string
+     */
+    public function getName(): string
+    {
+        return $this->name;
+    }

+    /**
+     * @return \DateTime
+     */
+    public function getRegisteredAt(): \DateTime
+    {
+        return $this->registeredAt;
+    }
}
```

### 1.3 Create `SalesmanDataFactory` class with `create` and `createFromSalesman` methods
```php
// src/Model/Salesman/SalesmanDataFactory.php

declare(strict_types=1);

namespace App\Model\Salesman;

class SalesmanDataFactory
{
    /**
     * @return \App\Model\Salesman\SalesmanData
     */
    public function create(): SalesmanData
    {
        $salesmanData = new SalesmanData();
        $salesmanData->registeredAt = new \DateTime();

        return $salesmanData;
    }

    /**
     * @param \App\Model\Salesman\Salesman $salesman
     * @return \App\Model\Salesman\SalesmanData
     */
    public function createFromSalesman(Salesman $salesman): SalesmanData
    {
        $salesmanData = new SalesmanData();
        $salesmanData->name = $salesman->getName();
        $salesmanData->registeredAt = $salesman->getRegisteredAt();

        return $salesmanData;
    }
}
```

### 1.4 Add `create`, `edit`, and `getById` methods into `SalesmanFacade` class
```diff
// src/Model/Salesman/SalesmanFacade.php

class SalesmanFacade
{
+    /**
+     * @param \App\Model\Salesman\SalesmanData $salesmanData
+     * @return \App\Model\Salesman\Salesman
+     */
+    public function create(SalesmanData $salesmanData): Salesman
+    {
+        $salesman = new Salesman($salesmanData);
+        $this->entityManager->persist($salesman);
+        $this->entityManager->flush();
+
+        return $salesman;
+    }
+
+    /**
+     * @param int $salesmanId
+     * @param \App\Model\Salesman\SalesmanData $salesmanData
+     * @return \App\Model\Salesman\Salesman
+     */
+    public function edit(int $salesmanId, SalesmanData $salesmanData): Salesman
+    {
+        $salesman = $this->getById($salesmanId);
+        $salesman->edit($salesmanData);
+        $this->entityManager->flush();
+
+        return $salesman;
+    }

+    /**
+     * @param $salesmanId
+     * @return \App\Model\Salesman\Salesman
+     */
+    public function getById($salesmanId): Salesman
+    {
+        return $this->salesmanRepository->getById($salesmanId);
+    }
}
```

### 1.5 Create new form defined by `SalesmanFormType` class
When using a grid for inline editing, a form is rendered in the grid row. We need to prepare that form now.
```php
// src/Form/Admin/SalesmanFormType.php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Salesman\SalesmanData;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class SalesmanFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter name']),
                ],
            ])
            ->add('registeredAt', DatePickerType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter date of registration']),
                ],
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SalesmanData::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}

```

### 1.6 Create new `SalesmanGridInlineEdit` class
Now, we have everything prepared and we are able to put it all together in new class (`SalesmanGridInlineEdit`) that will be responsible for the inline editing.
The class needs to extend `AbstractGridInlineEdit` and implement three methods -`getForm`, `editEntity`, and `createEntityAndGetId`.
We also have to inject the original `SalesmanGridFactory` to the new class constructor.
```php
// src/Grid/Salesman/SalesmanGridInlineEdit.php

namespace App\Grid\Salesman;

use App\Form\Admin\SalesmanFormType;
use App\Model\Salesman\SalesmanDataFactory;
use App\Model\Salesman\SalesmanFacade;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Symfony\Component\Form\FormFactoryInterface;

class SalesmanGridInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @var \App\Grid\Salesman\SalesmanGridFactory
     */
    private $salesmanGridFactory;

    /**
     * @var \App\Model\Salesman\SalesmanFacade
     */
    private $salesmanFacade;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \App\Model\Salesman\SalesmanDataFactory
     */
    private $salesmanDataFactory;

    public function __construct(
        SalesmanGridFactory $salesmanGridFactory,
        SalesmanFacade $salesmanFacade,
        FormFactoryInterface $formFactory,
        SalesmanDataFactory $salesmanDataFactory
    ) {
        parent::__construct($salesmanGridFactory);
        $this->salesmanGridFactory = $salesmanGridFactory;
        $this->salesmanFacade = $salesmanFacade;
        $this->formFactory = $formFactory;
        $this->salesmanDataFactory = $salesmanDataFactory;
    }

    /**
     * @param int|null $salesmanId
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm($salesmanId)
    {
        if ($salesmanId === null) {
            $salesmanData = $this->salesmanDataFactory->create();
        } else {
            $salesman = $this->salesmanFacade->getById($salesmanId);
            $salesmanData = $this->salesmanDataFactory->createFromSalesman($salesman);
        }

        return $this->formFactory->create(SalesmanFormType::class, $salesmanData);
    }

    /**
     * @param int $salesmanId
     * @param \App\Model\Salesman\SalesmanData $salesmanData
     */
    protected function editEntity($salesmanId, $salesmanData)
    {
        $this->salesmanFacade->edit($salesmanId, $salesmanData);
    }

    /**
     * @param \App\Model\Salesman\SalesmanData $salesmanData
     * @return int
     */
    protected function createEntityAndGetId($salesmanData)
    {
        $salesman = $this->salesmanFacade->create($salesmanData);

        return $salesman->getId();
    }
}
```
The new class must be registered in `services.yaml`:
```yaml
    App\Grid\Salesman\SalesmanGridInlineEdit: ~
```

### 1.7 Use `SalesmanGridInlineEdit` in `SalesmanController`
To make the salesman grid inline editable now, we need to use the `SalesmanGridInlineEdit::getGrid` method to get the grid instead of calling `SalesmanGridFactory::create` method directly:
```diff
// src/Controller/Admin/SalesmanController.php

namespace App\Controller\Admin;

-use App\Grid\Salesman\SalesmanGridFactory;
+use App\Grid\Salesman\SalesmanGridInlineEdit;
use App\Model\Salesman\SalesmanFacade;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;

class SalesmanController extends AdminBaseController
{
    /**
-     * @var \App\Grid\Salesman\SalesmanGridFactory
+     * @var \App\Grid\Salesman\SalesmanGridInlineEdit
     */
-    protected $salesmanGridFactory;
+    protected $salesmanGridInlineEdit;

    /**
     * @var \App\Model\Salesman\SalesmanFacade
     */
    protected $salesmanFacade;

-    public function __construct(SalesmanGridFactory $salesmanGridFactory, SalesmanFacade $salesmanFacade)
+    public function __construct(SalesmanGridInlineEdit $salesmanGridInlineEdit, SalesmanFacade $salesmanFacade)
    {
-        $this->salesmanGridFactory = $salesmanGridFactory;
+        $this->salesmanGridInlineEdit = $salesmanGridInlineEdit;
        $this->salesmanFacade = $salesmanFacade;
    }

    /**
     * @Route("/salesman/list/")
     */
    public function listAction()
    {
-        $grid = $this->salesmanGridFactory->create();
+        $grid = $this->salesmanGridInlineEdit->getGrid();

        return $this->render('Admin/Content/Salesman/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }
}
```

At this point, you should be able to edit and create new salesmen directly in the grid.

![Advanced grid with inline edit](img/advanced-grid-inline-edit.png)

## 2. Sort data manually (drag and drop)
In this part, we will enable drag and drop sorting of our salesmen using the grid. To make the changes in the ordering persistent, we need add new attribute to `Salesman` entity, first.

### 2.1 Add `$position` to `Salesman` entity and mark it as DB column using Doctrine ORM annotation
```diff
// src/Model/Salesman/Salesman.php

class Salesman
{
+    /**
+     * @var int|null
+     *
+     * @ORM\Column(type="integer", nullable=true)
+     */
+    protected $position;
}
```

### 2.2 Generate new database migration
Run phing target
```sh
php phing db-migrations-generate
```

The command prints a file name the migration was generated into.
The migration will look like this:
```php
namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20190305140005 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->sql('ALTER TABLE salesmen ADD position INT DEFAULT NULL');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}

```

### 2.3 Execute migrations to propagate all the changes to the database
Run phing target
```sh
php phing db-migrations
```

### 2.2 Make the `Salesman` entity implement `OrderableEntityInterface`
```diff
// src/Model/Salesman/Salesman.php

+ use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;

- class Salesman
+ class Salesman implements OrderableEntityInterface
{
+    /**
+     * @param int $position
+     */
+    public function setPosition($position)
+    {
+        $this->position = $position;
+    }
}
```

### 2.3 Enable drag and drop sorting in `SalesmanGridFactory`
```diff
// src/Grid/Salesman/SalesmanGridFactory.php

class SalesmanGridFactory implements GridFactoryInterface
{
    public function create(): Grid
    {
        ...
+       $grid->enableDragAndDrop(Salesman::class);
        ...
    {
}
```

Now you should be able to sort your salesmen using the cross icon in the left part of each row as a handle for drag a drop.

![Advanced grid with drag and drop](img/advanced-grid-drag-and-drop.png)

## Pitfalls
Be aware of using all the combinations that grid provides, e.g. it is not possible to use sorting by column when drag and drop is enabled.
