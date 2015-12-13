# Change log

## [3.1.0](https://github.com/Carrooi/Nette-ImagesManager/compare/3.0.0...3.1.0)
* Add presets for sizes and resize flags
* Fix tests for some environments
* Fix readme

## [3.0.0](https://github.com/Carrooi/Nette-ImagesManager/compare/2.1.4...3.0.0)
* Complete rewrite (huge BC break)
* Storing image version and showing it in URLs

## [2.1.4](https://github.com/Carrooi/Nette-ImagesManager/compare/2.1.3...2.1.4)
* Allow dash in images names

## [2.1.3](https://github.com/Carrooi/Nette-ImagesManager/compare/2.1.2...2.1.3)
* Optimize exceptions
* Throw an exception when uploading image with unknown name (eg. without file extension)
* Remove old image before reuploading even with different extension [#6](https://github.com/Carrooi/Nette-ImagesManager/issues/6)

## [2.1.2](https://github.com/Carrooi/Nette-ImagesManager/compare/2.1.1...2.1.2)
* Fix isImage and isNotImage latte macros for missing images

## [2.1.1](https://github.com/Carrooi/Nette-ImagesManager/compare/2.1.0...2.1.1)
* Fix url macros when using with custom objects
* Use CachedImagesStorage by default (replaces MemoryImagesStorage)

## [2.1.0](https://github.com/Carrooi/Nette-ImagesManager/compare/2.0.3...2.1.0)
* Optimized caching
* Refactored loading of default images
* Storing loaded random default image [#3](https://github.com/Carrooi/Nette-ImagesManager/issues/3)

## [2.0.3](https://github.com/Carrooi/Nette-ImagesManager/compare/2.0.2...2.0.3)
* Using only forward slashes at urls [#4](https://github.com/Carrooi/Nette-ImagesManager/issues/4)

## [2.0.2](https://github.com/Carrooi/Nette-ImagesManager/compare/2.0.1...2.0.2)
* Names of namespaces configured in DI can now contain any character

## [2.0.1](https://github.com/Carrooi/Nette-ImagesManager/compare/2.0.0...2.0.1)
* Fixed error with loading images without extension and they don't exists

## [2.0.0](https://github.com/Carrooi/Nette-ImagesManager/compare/1.1.0...2.0.0)
* Optimized tests
* Optimized dependencies of all classes
* Removed dependency on whole nette/nette and requiring only needed packages
* Require nette >= 2.2 (bc break)
* Latte macros src and image can return absolute urls
* Returning dummy images for thumbnails if no default image is found (uses [satyr.io](http://satyr.io/))
* Change base namespace from `DK` to `Carrooi` (bc break)
* Package and repository moved under Carrooi organization
* Packageist package renamed from `sakren/nette-images-manager` to `carrooi/images-manager` (bc break)

## [1.1.0](https://github.com/Carrooi/Nette-ImagesManager/compare/1.0.0...1.1.0)
* Some optimizations
* Upload method now uses quality from namespace configuration
* Added name resolvers
* Images can be loaded without their files' extensions
* Added caching (now using just in searching for files' extensions)
* Little bit better readme

## 1.0.0
* Initial version
