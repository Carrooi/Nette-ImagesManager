[![Build Status](https://img.shields.io/travis/Carrooi/Nette-ImagesManager.svg?style=flat-square)](https://travis-ci.org/Carrooi/Nette-ImagesManager)
[![Donate](https://img.shields.io/badge/donate-PayPal-brightgreen.svg?style=flat-square)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=39WBDGLHF48PE)

# Images manager

Images-manager is a tool for managing all of your web images (well, except template images).

## Installation

```
$ composer require sakren/nette-images-manager
$ composer update
```

now you can register extension and prepare new config file for images-manager.

*config.neon:*
```
extensions:
	images: DK\ImagesManager\DI\Extension
	
includes:
	- ./images.neon
```

*images.neon:*
```
images:
	basePath: %appDir%/../www/uploads/images
	baseUrl: /uploads/images
```

this is minimal configuration needed for images-manager.

## Namespaces

On your website you can have many different types of images. For example users' images, articles' images and so on. But you also want to keep them seperate and this is where namespaces came from. 

Actually namespace is just directory in chosen path (in our example `%appDir%/../www/uploads/images`)

This means that users' images will be saved here: `%appDir%/../www/uploads/images/users`.

Only thing you need to do is create this directory, along with all others you may need.

## Saving images

There is automatically registered "manager" service for handling all images operations, so lets include it and than use it (presenter will be enough for this example)

```php
namespace App\Presenters;

use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use DK\ImagesManager\ImagesManager;

/**
 *
 * @author David Kudera
 */
class ImagesPresenter extends Presenter
{


	/** @var \DK\ImagesManager\ImagesManager */
	private $imagesManager;
	
	
	/**
	 * @param \DK\ImagesManager\ImagesManager $imagesManager
	 */
	public function __construct(ImagesManager $imagesManager)
	{
		parent::__construct();
		
		$this->imagesManager = $imagesManager;
	}
	
	
	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentForm()
	{
		$form = new Form;
		
		$form->addUpload('image', 'Image')
			->addRule(Form::IMAGE);
			
		$form->addSubmit('save', 'Upload');
		
		$form->onSuccess[] = [$this, 'uploadImage'];
		
		return $form;
	}
	
	
	/**
	 * @param \Nette\Application\UI\Form $form
	 * @param \Nette\Utils\ArrayHash $values
	 */
	public function uploadImage(Form $form, ArrayHash $values)
	{
		if ($values->image->isOk()) {
			$image = $values->image->toImage();
			$namespace = 'users';
			$name = 'david.jpg';
			
			$this->imagesManager->upload($image, $namespace, $name);
			
			// @todo: show flash message and redirect
			
		} else {
			// @todo: show error
		}
	}

}
```

As you can see, isn't really simple, just call upload with desired image, namespace and final name. Thats it :-)

But be careful, if there is already image with name `david.jpg` in `users` namespace, it will be removed with all its thumbnails as well.

## Show in template

This step also couldn't be easier, because there are some Latte macros registered for you.

*original image:*
```html
<img n:src="users, 'david.jpg'">
```

*thumbnail with width:*
```html
<img n:src="users, 'david.jpg', 150">
```

*thumbnail with width and height:*
```html
<img n:src="users, 'david.jpg', '150x150'">
```

*thumbnail with different resize method (default is [fit](http://api.nette.org/2.2.2/source-Utils.Image.php.html#106-107)):*
```html
<img n:src="users, 'david.jpg', 150, stretch">
```

*absolute urls (works only at latte):*
```html
<img n:src="users, '//david.jpg'">
```

You can even use names without files' extensions and images-manager will try to find it for you:

```html
<img n:src="users, david, 100">
```

**Found files' extensions are cached, so if you change some image in other way than with this package, you'll have to 
delete cache by hand.**

### Other Latte macros

*image:*
```html
<strong>Image path:</strong> <i>{image users, 'david.jpg', '50x50'}</i>
```

*isImage:*
```html
<img n:isImage="users, 'david.jpg'" n:src="users, 'david.jpg', 50">
```

*isNotImage:*
```html
<div n:isNotImage="users, 'david.jpg'" class="alert alert-danger">
	Upload your image now!
</div>
```

## Lists of images

Imagine that you've got few example images with male avatars and few with female avatars. Now you want to get a list of them, so you can offer these gender specific avatars to your users.

*images.neon:*
```
images:
	
	namespaces:
	
		users:
			lists:
				male:
					- male01.jpg
					- male02.jpg
					- male03.jpg
				
				female:
					- female01.jpg
					- female02.jpg
					- female03.jpg
```

```php
$maleAvatars = $imagesManager->getList('users', 'male');
$femaleAvatars = $imagesManager->getList('users', 'female');
```

## Default images

Maybe you will want some default image. Users are again great example, because it is quite usual to have some default avatar. Default name of default image is `default.jpg` and it needs to be in desired namespace directory.

*images.neon:*
```
images:

	default: avatar.jpg
```

`default` option in configuration can be also array of names. Then every time, this default image is needed, it will be some random image from this array.

*images.neon:*
```
images:
	
	default:
		- male.jpg
		- female.jpg
```

There is also possibility to change that name "on the fly", which can come in handy for example in male / female default avatars.

```html
<img n:src="users, 'david.jpg', 50, null, 'male.jpg'">
```

Notice that 4th parameter is null and that's because we do not want to change resize method.

## Dummy images with [satyr.io](http://satyr.io/)

When even default image is missing, this package will load dummy image from [satyr.io](http://satyr.io/) service. This 
only works for images with known size.

### Name resolvers

With default setup, you have to use string names like `david.jpg`. But for users it would be better to use eg. their 
entities:

```html
{foreach $users as $userEntity}
	<img n:src="users, $userEntity, 150">
{/foreach}
```

Now you'll be able for example decide if you need to show male or female default photo much more easily. You only have to 
register custom name resolver.

```php
namespace App\Images;

use DK\ImagesManager\INameResolver;
use App\Model\Entities\User;
use Exception;

class UserEntityNameResolver implements INameResolver
{


	/**
	 * @param \App\Model\Entities\User $user
	 * @return string
	 */
	public function translateName($user)
	{
		if (!$user instanceof User) {
			throw new Exception;		// todo: better exception
		}
		
		return $user->id. '.'. $user->avatarType;		// translates to eg. 5.png
	}


	/**
	 * @param \App\Model\Entities\User $user
	 * @return string
	 */
	public function getDefaultName($user)
	{
		if (!$user instanceof User) {
			throw new Exception;		// todo: better exception
		}
		
		return $user->gender->name. '.png';
	}

}
```

If you return something from `getDefaultName` method, you'll overwrite defaults from your configuration. You can leave that 
method empty and defaults from neon configuration will be used.

now just register your resolver for `users` namespace:

*images.neon:*
```
images:

	namespaces:
	
		users:
			nameResolver: App\Images\UserEntityNameResolver
```

## Loading images in PHP

```php
$image = $imagesManager->load('users', 'david.jpg');
```

This `load` method is used in `src` and `image` macros, so arguments are completely same. Also it try to find needed thumbnail or create a new one.

**When image is not found (even default image), exception will be thrown!**

If you need some way to not throw an exception when image is not found, you can of course catch it, or use `createImage` method.

```php
$image = $imagesManager->createImage('users', 'david.jpg');
```

`createImage` also does not work with default images.

## Removing images

*Remove image with thumbnails:*
```php
$imagesManager->removeImage($image);
```

*Remove just thumbnails:*
```php
$imagesManager->removeThumbnails($image);
```

## Find all images in namespace

```php
$images = $imagesManager->findImages('users');
```

## Find all thumbnails by image

```php
$thumbnails = $imagesManager->findThumbnails($image);
```

## Default configuration

*images.neon:*
```
images:

	nameResolver: DK\ImagesManager\DefaultNameResolver
	cacheStorage: @cacheStorage
	resizeFlag: fit
	default: default.jpg
	quaility: null
	basePath: null
	baseUrl: null
	caching: true
	
	mask:
		images: <namespace><separator><name>.<extension>
		thumbnails: <namespace><separator><name>_<resizeFlag>_<size>.<extension>
		
	namespaces: []
```

Some of these options can be changed for specific namespace:

*images.neon:*
```
images:

	# ...
	
	namespaces:
	
		users:
			nameResolver: @App\CustomNameResolver
			default: avatar.png
			resizeFlag: stretch
			quality: 100
```

with this, you can set default option from list

*images.neon:*
```
images:

	namespaces:
		users:
			lists:
				avatars:
					- male01.png
					- male02.png
					- female01.png
					- female02.png
					
			default: <list|avatars>
```

# DK\ImagesManager\Image

All methods which returns images, returns `DK\ImagesManager\Image` class.

**Methods:**

| Name                 | Arguments           | Description                                                                    |
| -------------------- | ------------------- | ------------------------------------------------------------------------------ |
| `getNamespace`       |                     |                                                                                |
| `getName`            | `$full = true`      | If `$full` is `true`, file's extension will be appended                        |
| `setName`            | `$name`             | Name in format: `<name>.<extension>`                                           |
| `getExtension`       |                     | Returns image's file extension                                                 |
| `getSize`            |                     | Returns int for width or size in format: `<width>x<height>`                    |
| `setSize`            | `$size`             | `$size` must be integer for just width or string in format: `<width>x<height>` |
| `getWidth`           |                     |                                                                                |
| `setWidth`           | `$width`            |                                                                                |
| `getHeight`          |                     |                                                                                |
| `setHeight`          | `$height`           |                                                                                |
| `getResizeFlag`      |                     |                                                                                |
| `setResizeFlag`      | `$flag`             |                                                                                |
| `getBasePath`        |                     |                                                                                |
| `setBasePath`        | `$path`             |                                                                                |
| `getBaseUrl`         |                     |                                                                                |
| `setBaseUrl`         | `$url`              |                                                                                |
| `getImagesMask`      |                     |                                                                                |
| `setImagesMask`      | `$mask`             |                                                                                |
| `getThumbnailsMask`  |                     |                                                                                |
| `setThumbnailsMask`  | `$mask`             |                                                                                |
| `isThumbnail`        |                     | Returns `true` if size is set                                                  |
| `getOriginalPath`    |                     | Returns path to original file (from thumbnail image)                           |
| `getPath`            |                     | Returns file path to image                                                     |
| `isExists`           |                     | Returns true if image exists in file system                                    |
| `getUrl`             | `$absolute = false` | Returns url to image                                                           |
| `tryCreateThumbnail` | `$quality = null`   | Create and save new thumbnail (size must be set)                               |

# Changelog

* 1.1.0
	+ Some optimizations
	* Upload method now uses quality from namespace configuration
	* Added name resolvers
	* Images can be loaded without their files' extensions
	* Added caching (now using just in searching for files' extensions)
	* Little bit better readme

* 1.0.0
	+ Initial version
