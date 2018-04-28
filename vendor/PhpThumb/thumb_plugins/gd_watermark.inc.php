<?php
/**
 * Edited by chiplove.9xpro
*/
class GdWatermarkLib
{
	/**
	* Instance of GdThumb passed to this class
	*
	* @var GdThumb
	*/
	protected $parentInstance;
	protected $currentDimensions;
	protected $workingImage;
	protected $newImage;
	protected $options;

	// ct = center top
	// cc = center center
	// lt = left top
	// rt = right top
	// lb = left bottom
	// rb = right bottom
	// cb = center bottom

	public function createWatermark ($mask_file, $mask_position='cc', $mask_padding=0, $that)
	{
		// bring stuff from the parent class into this class...
		$this->parentInstance = $that;
		$this->currentDimensions = $this->parentInstance->getCurrentDimensions();
		$this->workingImage = $this->parentInstance->getWorkingImage();
		$this->newImage = $this->parentInstance->getOldImage();
		$this->options = $this->parentInstance->getOptions();

		$this->mask_file = $mask_file;
		$this->mask_position = $mask_position;
		$this->mask_padding = $mask_padding;

		$canvas_width = $this->currentDimensions['width'];
		$canvas_height = $this->currentDimensions['height'];

		list($logo_width, $logo_height, $logo_type, $logo_attr) = getimagesize($mask_file);

		switch ($logo_type)
		{
			case 1:
				$logo_image = imagecreatefromgif($mask_file);
				break;
			case 2:
				@ini_set('gd.jpeg_ignore_warning', 1);
				$logo_image = imagecreatefromjpeg($mask_file);
				break;
			case 3:
				$logo_image = imagecreatefrompng($mask_file);
				break;
		}

		imagealphablending($this->workingImage, true);

		switch($mask_position)
		{
			// Random
			case 'rd':
				$start_width = round(mt_rand($mask_padding, $canvas_width - $logo_width - $mask_padding));
				$start_height = round(mt_rand($mask_padding, $canvas_height - $logo_height - $mask_padding));
				break;
			// Center top
			case 'ct':
			case 'tc':
				$start_width = round(($canvas_width - $logo_width) / 2);
				$start_height = $mask_padding;
				break;
			// Center middle
            case 'mc':
            case 'cm':
			case 'cc':
				$start_width = round(($canvas_width  - $logo_width) / 2);
				$start_height = round(($canvas_height - $logo_height) / 2);
				break;
			// Center bottom
			case 'cb':
			case 'bc':
				$start_width = round(($canvas_width - $logo_width) / 2);
				$start_height = $canvas_height - $mask_padding - $logo_height;
				break;
			// Left top
			case 'lt':
			case 'tl':
				$start_width = $mask_padding;
				$start_height = $mask_padding;
				break;
			// Left center
			case 'lc':
			case 'cl':
				$start_width = $mask_padding;
				$start_height = round(($canvas_height - $logo_height) / 2);
				break;
			// Left bottom
			case 'lb':
			case 'bl':
				$start_width = $mask_padding;
				$start_height = $canvas_height - $mask_padding - $logo_height;
				break;
			// Right top
			case 'rt':
			case 'tr':
				$start_width = $canvas_width - $mask_padding - $logo_width;
				$start_height = $mask_padding;
				break;
			// Right center
			case 'rc':
			case 'cr':
				$start_width = $canvas_width - $mask_padding - $logo_width;
				$start_height = round(($canvas_height - $logo_height) / 2);
				break;
			// Right bottom
			case 'rb':
			case 'br':
			default:
				$start_width = $canvas_width - $logo_width - $mask_padding;
				$start_height = $canvas_height - $logo_height - $mask_padding;
				break;
		}

		imagecopy( $this->workingImage, $logo_image, $start_width, $start_height, 0, 0, $logo_width, $logo_height );
		imagedestroy( $logo_image );

		return $that;
	}
}

$pt = PhpThumb::getInstance();
$pt->registerPlugin('GdWatermarkLib', 'gd');
