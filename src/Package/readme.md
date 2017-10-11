# Package
This library contains tools for working with Packages programmatically.

## Definitions

### Package
A package is a collection of related classes that forms a single unit of functionality. Much like a class is a
  collection of interdependent functions and state, a package is a collection of interdependent classes, and by
  definition, state as well.

Just as a class has a public API that the outside world can interact with, so does a Package. Classes may be defined as
 public, in that they're intended to be used as integration points to access the Package's functionality, and private,
 declaring that they're internal to the Package and shouldn't be used directly.
