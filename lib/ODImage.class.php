<?php
include_once("./lib/logging.php");
include_once("./lib/theme.php");

$GD_IMAGE_TYPES = array (
		'png' => array (
				'extension' => 'png',
				'ctype' => 'image/png',
				'sendfunc' => 'ImagePNG',
				'createfunc' => 'ImageCreateFromPNG' ),
		'jpg' => array (
				'extension' => 'jpg',
				'ctype' => 'image/jpeg',
				'sendfunc' => 'ImageJPEG',
				'createfunc' => 'ImageCreateFromJPEG' ),
		'gif' => array (
				'extension' => 'gif',
				'ctype' => 'image/gif',
				'sendfunc' => 'ImageGIF',
				'createfunc' => 'ImageCreateFromGIF' ) );

function is_function_valid($function) {
	$disabled_functions = @ini_get ( "disable_functions" );
	if (strlen ( $disabled_functions ) > 0) {
		$disabled_functions_r = explode ( ',', $disabled_functions );
		return (function_exists ( $function ) == true && array_search ( $function, $disabled_functions_r ) === FALSE);
	} else {
		return function_exists ( $function );
	}
}

class ODImage {
	var $_imgType;
	var $_image;
	var $_imageSrc;
	var $_image_type_r;
	var $_errors;

	function __construct($imgType = 'auto') {
		global $GD_IMAGE_TYPES;
		
		if ($imgType != NULL && $imgType != 'auto') {
			if ($this->isImageTypeValid ( $imgType ) !== FALSE) {
				$this->_imgType = $imgType;
				$this->_image_type_r = $this->getImageTypeConfig ( $imgType );
			} else {
				$this->addError ( 'Illegal Image Type: ' . $imgType );
				return NULL;
			}
		} else {
			// else choose automatically based on support of functions
			reset ( $GD_IMAGE_TYPES );
			foreach ( $GD_IMAGE_TYPES as $imgType => $image_type_r ) {
				if ($this->isImageTypeValid ( $imgType ) !== FALSE) {
					$this->_imgType = $imgType;
					$this->_image_type_r = $image_type_r;
					break;
				}
			}
		}
	}

	function addError($error) {
		$this->_errors [] = $error;
	}

	function getErrors() {
		return $this->_errors;
	}

	function getImageType() {
		return $this->_imgType;
	}

	function getImageSrc() {
		return $this->_imageSrc;
	}

	function getImageExtension() {
		return $this->_image_type_r ['extension'];
	}

	function getImageContentType() {
		return $this->_image_type_r ['ctype'];
	}

	function getImageTypeConfig($imgType = NULL) {
		global $GD_IMAGE_TYPES;
		
		if ($imgType == NULL) {
			$imgType = $this->getImageType ();
		}
		
		if (isset($GD_IMAGE_TYPES [$imgType]) && is_array($GD_IMAGE_TYPES[$imgType])) {
			return $GD_IMAGE_TYPES [$imgType];
		} else {
			return FALSE;
		}
	}

	function isImageTypeValid($imgType) {
		$image_config_r = $this->getImageTypeConfig ( $imgType );
		if ($image_config_r !== FALSE) {
			if (! is_function_valid ( $image_config_r ['sendfunc'] )) {
				$this->addError ( 'Image Send function not found:' . $image_config_r ['sendfunc'] );
				return FALSE;
			}
			
			if (! is_function_valid ( $image_config_r ['createfunc'] )) {
				$this->addError ( 'Image Create function not found: ' . $image_config_r ['createfunc'] );
			}
			
			return TRUE;
		} else {
			$this->addError ( 'Image Config not found: ' . $imgType );
		}
		
		return FALSE;
	}

	function &getImage() {
		if ($this->_image !== NULL) {
			return $this->_image;
		} else {
			$this->addError ( 'Image not created or already sent' );
			return FALSE;
		}
	}

	/**
	 * @param unknown_type $image - should be the name minus any extension
	 */
	function createImage($image) {
		$this->_imageSrc = $this->_getImageSrc ( $image );
		if ($this->_imageSrc !== FALSE) {
			$createFunc = $this->_image_type_r ['createfunc'];
			$this->_image = $createFunc ( $this->_imageSrc );
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function sendImage() {
		Header ( "Content-Type: " . $this->getImageContentType () );
		
		$sendFunc = $this->_image_type_r ['sendfunc'];
		
		$sendFunc ( $this->_image );
		
		// destroy image when done
		ImageDestroy ( $this->_image );
		
		unset ( $this->_image );
	}

	function _getImageSrc($name) {
		if (strpos ( $name, '.' ) === FALSE) {
			$filename = $name . '.' . $this->getImageExtension ();
			
			$src = theme_image_src ( $filename );
			if ($src !== FALSE) {
				return $src;
			} else {
				$this->addError ( 'Name source not found: ' . $filename );
				return FALSE;
			}
		} else {
			$this->addError ( 'Name must not include extension: ' . $name );
			return FALSE;
		}
	}
}
?>
