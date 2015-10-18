# Images manager

[![Build Status](https://img.shields.io/travis/Carrooi/Nette-ImagesManager.svg?style=flat-square)](https://travis-ci.org/Carrooi/Nette-ImagesManager)
[![Donate](https://img.shields.io/badge/donate-PayPal-brightgreen.svg?style=flat-square)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=39WBDGLHF48PE)

Simple to use tool for managing uploaded images.

## BC break!

**Be careful, version 3.0 was completely rewritten.**

If you are using just latte templates and uploading images, it should be enough just to move `basePath` and `baseUrl` 
configuration under the `source` section.

## Installation

```
$ composer require carrooi/images-manager
$ composer update
```

**config.neon:**

```yaml
extensions:
	images: Carrooi\ImagesManager\DI\ImagesManagerExtension
    
images:
	source:
    	basePath: %appDir%/../www/uploads
        baseUrl: http://www.site.com/uploads
```

## Namespaces

On your website you can have many different types of images. For example users' images, articles' images and so on. But 
you also want to keep them separate and this is where namespaces came from. 

Namespace is actually just a directory in chosen path (in our example `%appDir%/../www/uploads`)

This means that users' images will be saved here: `%appDir%/../www/uploads/user`.

Only thing you need to do is create this directory.

## Saving images

There is automatically registered "manager" service for handling all images operations, so lets include it and than use 
it (presenter will be enough for this example)

```php
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;

class ImagesPresenter extends Presenter
{


	/** @var \Carrooi\ImagesManager\ImagesManager @inject */
	public $imagesManager;
	
	
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
	 * @param mixed $values
	 */
	public function uploadImage(Form $form, $values)
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

As you can see, isn't really simple, just call upload with desired image, namespace and final name. That's it :-)

But be careful, if there is already image with name `david.jpg` in `users` namespace, it will be removed with all its 
thumbnails as well.

## Latte templates

This step also couldn't be easier, because there are some Latte macros prepared for you.

**original image:**
```html
<img n:src="users, 'david.jpg'">
```

**thumbnail with width:**
```html
<img n:src="users, 'david.jpg', 150">
```

**thumbnail with width and height:**
```html
<img n:src="users, 'david.jpg', '150x150'">
```

**thumbnail with different resize method (default is [fit](http://api.nette.org/2.2.2/source-Utils.Image.php.html#106-107)):**
```html
<img n:src="users, 'david.jpg', 150, 'shrinkOnly|stretch'">
```

You can even use names without files' extensions and images-manager will try to find it for you:

```html
<img n:src="users, david, 100">
```

**Found files' extensions are cached, so if you change some image in other way than with `ImagesManager`, you'll have to 
delete the cache yourself.**

### Other Latte macros

**image:**
```html
<strong>Image path:</strong> <i>{image users, 'david.jpg', '50x50'}</i>
```

**is-image (with alias isImage):**
```html
<img n:is-image="users, 'david.jpg'" n:src="users, 'david.jpg', 50">
```

**is-not-image (with alias isNotImage):**
```html
<div n:is-not-image="users, 'david.jpg'" class="alert alert-danger">
	Upload your image now!
</div>
```

## Default images

Maybe you will want some default image. Users are again great example, because it is quite usual to have some default 
avatar. Default name of default image is `default.jpg` and it needs to be in desired namespace directory.

```yaml
images:
	default: default.png
```

### Dummy images

When even default image does not exists, you can show some dummy image (like cute cats). This is possible because of 
[lorempixel](http://lorempixel.com/) service.

```yaml
dummy:
	enabled: true
    category: cats
    fallbackSize: [800, 600]
    chance: 100
```

* `fallbackSize`: image resolution used when no size is given in latte template
* `chance`: percentage chance that instead of no image, you'll see cute cat

## Entities, DTOs and so on instead of string names

With default setup, you have to use string names like `david.jpg`. But for users it would be better to use eg. their 
entities directly. You just have to configure custom namespace setup with own name resolver.

```php
use Carrooi\ImagesManager\INameResolver;
use App\Model\Entities\User;

class UserEntityNameResolver implements INameResolver
{


	/**
	 * @param \App\Model\Entities\User $user
	 * @return string
	 */
	public function translateName($user)
	{
		if (!$user instanceof User) {
			throw new \Exception;		// todo: better exception
		}
		
		return $user->getId();		// just like with string names
	}


	/**
	 * @param \App\Model\Entities\User $user
	 * @return string
	 */
	public function getDefaultName($user)
	{
		if (!$user instanceof User) {
			throw new \Exception;		// todo: better exception
		}
		
		return $user->getGender()->getName();
	}

}
```

**configuration:**

```yaml
images:
	namespaces:
		user:
			nameResolver: App\Images\UserEntityNameResolver
```

## Quality of images

Quality of jpg and png images can be customized. This can be done either globally for all image namespaces or for each 
image namespace separately.

```yaml
images:
	quality: 90
    namespaces:
    	user:
        	quality: 100
```
