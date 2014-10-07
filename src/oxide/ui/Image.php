<?php
namespace oxide\ui;
use oxide\util\Stringify;
use oxide\validation\ValidationResult;


/**
 * Image class to represent single supported image in memory
 * 
 */
class Image
{
	private
      $_file = null,
		$_image = null,
		$_quality = 90,
		$_mimetype = null,
		$_properties = array(),
      $_width = 0,
      $_height = 0,
		$_initsize = 0;

   const
      HEIGHT = 'height',
      WIDTH = 'width';
      
	/**
	 * construct the image
	 * 
	 * @param string $file
	 */
	public function  __construct($file)
	{
		if(!is_file($file)) {
			throw new \oxide\util\Exception("$file does not exists");
		}

      $this->_file = $file;
		$this->_initsize = filesize($file);
		$properties = getimagesize($file);
      
		
		if(!$properties) {
			throw new \oxide\util\Exception("incorrect image file type");
		}
      
      $this->_width = $properties[0];
      $this->_height = $properties[1];
		$this->_properties = $properties;
		$this->_mimetype = image_type_to_mime_type($properties[2]);
	}
   
   public static function createFromFile($file)
   {
      return new Image($file);
   }
   
   /**
    * Returns image information returned by getimagesize function
    * 
    * @return array
    */
   public function getImageInfo()
   {
      return $this->_properties;
   }
   
   /**
    * Load the current image into memory
    * 
    * @param ValidationResult $result Holds result of image loading
    * @return bool indicate if loading was successful or not
    */
   public function load(ValidationResult $result = null)
   {
      if(!$result) $result = new ValidationResult();
      
      $properties = $this->_properties;
      $file = $this->_file;
      
		// create the image
		switch ($properties[2]) {
			case IMAGETYPE_JPEG:
				$this->_image = imagecreatefromjpeg($file);
				break;

			case IMAGETYPE_GIF:
				$this->_image = imagecreatefromgif($file);
				break;

			case IMAGETYPE_PNG:
				$this->_image = imagecreatefrompng($file);
				break;

			default:
				$result->addError("Image type is not supported");
		}  
      
      if(!$this->_image) {
         // image wasn't loaded
         $result->addError('Unable to load the image');
      }
      
      
      return $result->isValid();
   }

	/**
	 *
	 * @param int $quality
	 */
	public function setQuality($quality)
	{
		if($quality < 1 || $quality > 100) {
			$quality = 75;
		}
		
		$this->_quality = $quality;
	}

	/**
	 * gets the current quality
	 * 
	 * @return int
	 */
	public function getQuality()
	{
		return $this->_quality;
	}

	/**
	 * returns the file size of the image that initially created
	 *
	 * This value will not reflect resized file size
	 * @return int
	 */
	public function getInitialFileSize()
	{
		return $this->_initsize;
	}

	/**
	 * returns current image mime type
	 * 
	 * @return string
	 */
	public function getMimeType()
	{
		return $this->_mimetype;
	}
   
   public function getHeight() 
   {
      return $this->_height;
   }
   
   
   public function getWidth()
   {
      return $this->_width;
   }
   
   /**
    * Resizes the current image into given $max_width and $max_height
    * 
    * @param int $max_width
    * @param int $max_height
    * @throws \Exception
    * @return boolean Indicates if resize was successful.
    */
   public function resize($max_width = 0, $max_height = 0)
   {
      if(!$this->_image) throw new \Exception('Image isn\'t loaded.');
      
      
      $current_width = $this->_properties[0];
		$current_height = $this->_properties[1];
      
      if($max_height == 0) $max_height = $current_height;
      if($max_width == 0)  $max_width  = $current_width;

      
      $new_width = 0;
      $new_height = 0;

      if($current_width > $current_height) { // width is bigger then hieght
        $new_width = $max_width;
        $new_height = floor(
            $current_height*($max_width/$current_width)
        );
      }
      else if($current_width < $current_height) { // height is bigger then width
        $new_height = $max_height;
        $new_width = floor(
            $current_width*($max_height/$current_height)
        );
      }
      else { // squared image
        $new_width = $max_width;
        $new_height = $max_height;
      }
      
      $copy = imagecreatetruecolor($new_width, $new_height);
      $success = imagecopyresampled($copy,$this->_image,0,0,0,0,$new_width, $new_height, $current_width, $current_height);
      imagedestroy($this->_image);

      if($success) {
         $this->_image = $copy;
         $this->_width = $new_width;
         $this->_height = $new_height;
      }
      
      return $success;
   }

	/**
	 * return png quality converted from 1 - 100 scale
	 * 
	 * @return int
	 */
	private function _getPngQuality()
	{
		$pngQuality = ($this->_quality - 100) / 11.111111;
		$pngQuality = round(abs($pngQuality));
		return $pngQuality;
	}
	
	private function _calculateReduction($thumbnailsize){
		 $srcW = $this->_properties[0];
		 $srcH = $this->_properties[1];
		 //adjust
		 if($srcW < $srcH){
				 $reduction = round($srcH/$thumbnailsize);
		 }else{
				 $reduction = round($srcW/$thumbnailsize);
		 }
		 return $reduction;
	}
   
   public function save($file)
   {
      
   }
   
   /**
    * 
    * @param type $file
    * @throws \Exception
    */
   public function output($file = null)
   {
      if(!$this->_image) throw new \Exception('Image isn\'t loaded.');   
      
      if(!$file) { // no file given, we will render right away
         // create clean header
         header_remove();
         header("Content-type: {$this->_mimetype}");
      }
		
      $success = true;
      switch($this->_properties[2]){
          case IMAGETYPE_JPEG:
              $success = imagejpeg($this->_image, $file, $this->_quality);
              break;
          case IMAGETYPE_GIF:
              $success = imagegif($this->_image, $file);
              break;
          case IMAGETYPE_PNG:
              $success = imagepng($this->_image, $file, $this->_getPngQuality());
              break;
          default:
              ;
      }
      
      
      return $success;
   }
	
   /**
    * release memory + cleanup
    */
	public function __destruct()
	{
    if(isset($this->_image)){
        imagedestroy($this->_image);
    }
	}

}