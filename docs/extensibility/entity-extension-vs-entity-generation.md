# Entity Extension vs. Entity Generation

When we extracted base functionality into the separate package [shopsys/framework](https://github.com/shopsys/framework), we considered two main ways to extend its entities in other modules and the project repository.
The first way, which is currently implemented, we call [entity extension](./entity-extension.md), and it allows adding and modifying properties and behavior using class inheritance.
The second way is to generate the entity classes used in the application by combining information from all modules and the project repository.

This article should explain the advantages and disadvantages of both solutions and describe the reasons behind our choice.

## Entity extension

Base entities are extended using class inheritance.
Support of this extension by Doctrine is based on event subscribers and metadata manipulation.
If an extension is defined, it requires using the extended entity instead of the parent entity in the whole application.

Details about internal workings of the implemented solution are described in the [Entity Extension](./entity-extension.md) article.

### Class inheritance as a double-edged sword

Class inheritance is a concept that object-oriented programmers are used to.
All entity-related work can be done in a PHP class with all the comfort of IDE auto-completion, coding-standards etc.
New-comers to Shopsys Platform can learn how to extend entities earlier due to this.

The greatest disadvantage comes from the fact that you cannot extend multiple classes at once in PHP.
In a nutshell, this means that a base entity cannot be extended by independent modules.
In case two modules extended one entity, we would end up with two extended entities, unable to use them both at once in our application.

This means that all independent modules that want to extend an entity have to define their own entities with a 1:1 relation, even for storing a single attribute.
It prevents normalization of the database and may lead to lower performance in some cases.

### Compatibility with other Doctrine extensions

A few Doctrine extensions in Shopsys Platform manipulate meta-data of entities.
As entity extension relies on meta-data manipulation as well, there were a few conflicts requiring an extra Doctrine event listener and specific order of execution.

Ensuring compatibility with an entity extension mechanism is something to be kept in mind when adding a new Doctrine extension.

## Entity generation

We considered generating the entities automatically by aggregating entity requirements from all bundles.
Imagine having a basic definition of a `Product` entity in [shopsys/framework](https://github.com/shopsys/framework).
You could provide additional definitions (e.g., add a new attribute) in any module or your project.
Then, a command could be executed that would generate a `Product` entity, which would include all required properties and methods and would be used in the whole application.

### How to define an entity in a module

Any module could define a new entity or extend an already defined one.
This could be done either via a configuration file (XML or YAML format) or via a PHP class.

A disadvantage of using configuration files is that it cannot define behavior, which would lead to a strictly anemic domain model.
On the other hand, combining PHP classes that would allow defining behavior in methods is much more complicated and error-prone.

### Troubles of generating classes

All modules would have to use the generated entity class.
This class is not defined in the committed code, which may be confusing while browsing the repository and it would complicate the build process for code checking and static analysis.
Having uncommitted generated classes might complicate work for programmers, e.g., preventing them from using refactoring tools in their IDEs.
Also, all modules, framework, and project wouldn't contain full-featured entities, always only pieces of entities, and that would be difficult to orient in.

Any modification to an entity in your project would require executing a command before the changes can take effect.
This delay and extra workflow step could lead to unexpected issues and a worse developer experience.

## Our Decision

We decided to entity extension via class inheritance as it is the more straightforward and easy-to-use approach.
We believe that database normalization is not that important on its own.
The potential performance loss can be addressed in the future by circumventing Postgres altogether for read operations that need to run fast (e.g., by using Elasticsearch).

Entity extension is a better choice from the point of view of project developers because of its simplicity and intuitiveness.

If the inability of modules to alter base entities proves to be a problem in the future, we may consider a combination of both ways.
Information from the modules could be combined, providing a way for modules to affect base entities.
The generated entity could be further extended in the project, avoiding the need for regeneration after every single change.

The possibility of mitigating the disadvantages and the much easier implementation of en entity extension approach convinced us to choose this way.
