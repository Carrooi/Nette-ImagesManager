<?php

namespace Carrooi\ImagesManager;

class RuntimeException extends \RuntimeException {}

class ImageNotExistsException extends RuntimeException {}

class InvalidStateException extends RuntimeException {}

class InvalidImageNameException extends RuntimeException {}

class InvalidArgumentException extends \InvalidArgumentException {}

class ConfigurationException extends RuntimeException {}
