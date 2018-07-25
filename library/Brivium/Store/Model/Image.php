<?php

/**
 * Model for images.
 *
 * @package Brivium_Store
 */
class Brivium_Store_Model_Image extends XenForo_Model
{
	/**
	 * List of available image sizes. The largest must go first.
	 * Images of each size code (directory name) will be no bigger
	 * than the given pixel amount.
	 *
	 * @var array Format: [code] => max pixels
	 */
	protected static $_sizes = array(
		'o' => 0,
		'l' => 192,
		'm' => 96,
		's' => 48,
	);

	public static $imageQuality = 85;
	/**
	 * Processes an image upload for a content.
	 *
	 * @param XenForo_Upload $upload The uploaded image.
	 * @param integer $contentId Content ID image belongs to
	 * @param array|false $permissions User's permissions. False to skip permission checks
	 *
	 * @return array Changed image fields
	 */
	public function uploadImage(XenForo_Upload $upload, $contentId, $permissions)
	{
		if (!$upload->isValid())
		{
			throw new XenForo_Exception($upload->getErrors(), true);
		}

		if (!$upload->isImage())
		{
			throw new XenForo_Exception(new XenForo_Phrase('uploaded_file_is_not_valid_image'), true);
		};

		$imageType = $upload->getImageInfoField('type');
		//prd($imageType);
		if (!in_array($imageType, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)))
		{
			throw new XenForo_Exception(new XenForo_Phrase('uploaded_file_is_not_valid_image'), true);
		}

		$baseTempFile = $upload->getTempFile();

		$width = $upload->getImageInfoField('width');
		$height = $upload->getImageInfoField('height');

		return $this->applyImage($contentId, $baseTempFile, $imageType, $width, $height, $permissions);
	}

	/**
	 * Applies the image file to the specified user.
	 *
	 * @param integer $contentId
	 * @param string $fileName
	 * @param constant|false $imageType Type of image (IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)
	 * @param integer|false $width
	 * @param integer|false $height
	 * @param array|false $permissions
	 *
	 * @return array
	 */
	public function applyImage($contentId, $fileName, $imageType = false, $width = false, $height = false, $permissions = false)
	{
		
		if (!$imageType || !$width || !$height)
		{
			$imageInfo = getimagesize($fileName);
			if (!$imageInfo)
			{
				throw new XenForo_Exception('Non-image passed in to applyImage');
			}
			$width = $imageInfo[0];
			$height = $imageInfo[1];
			$imageType = $imageInfo[2];
		}
		
		if (!in_array($imageType, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)))
		{
			throw new XenForo_Exception('Invalid image type passed in to applyImage');
		}

		if (!XenForo_Image_Abstract::canResize($width, $height))
		{
			throw new XenForo_Exception(new XenForo_Phrase('uploaded_image_is_too_big'), true);
		}

		$outputFiles = array();
		$originalImageType = image_type_to_extension($imageType);
		
		$outputType = $imageType;

		reset(self::$_sizes);
		
		list($sizeCode, $maxDimensions) = each(self::$_sizes);
		$newTempFile = tempnam(XenForo_Helper_File::getTempDir(), 'xf');
		copy($fileName, $newTempFile); // no resize necessary, use the original
		$outputFiles[$sizeCode] = $newTempFile;
		
		list($sizeCode, $maxDimensions) = each(self::$_sizes);
		$shortSide = ($width > $height ? $height : $width);
		if ($shortSide > $maxDimensions)
		{
			$newTempFile = tempnam(XenForo_Helper_File::getTempDir(), 'xf');
			//print_r($newTempFile);die;
			$image = XenForo_Image_Abstract::createFromFile($fileName, $imageType);
			if (!$image)
			{
				throw new XenForo_Exception(new XenForo_Phrase('image_could_be_processed_try_another_contact_owner'), true);
			}
			$image->thumbnailFixedShorterSide($maxDimensions);
			$image->output($outputType, $newTempFile, self::$imageQuality);

			$width = $image->getWidth();
			$height = $image->getHeight();

			$outputFiles[$sizeCode] = $newTempFile;
		}
		else
		{
			$outputFiles[$sizeCode] = $fileName;
		}
				while (list($sizeCode, $maxDimensions) = each(self::$_sizes))
		{
			$newTempFile = tempnam(XenForo_Helper_File::getTempDir(), 'xf');
			$image = XenForo_Image_Abstract::createFromFile($fileName, $imageType);
			if (!$image)
			{
				continue;
			}
			if($maxDimensions){
				$image->thumbnailFixedShorterSide($maxDimensions);
			}
			if ($image->getOrientation() != XenForo_Image_Abstract::ORIENTATION_SQUARE)
			{
				$image->crop(floor(($image->getWidth() - $maxDimensions) / 2), floor(($image->getHeight() - $maxDimensions) / 2), $maxDimensions, $maxDimensions);
			}
			$image->output($outputType, $newTempFile, self::$imageQuality);
			unset($image);

			$outputFiles[$sizeCode] = $newTempFile;
			
		}
		
		if (count($outputFiles) != count(self::$_sizes))
		{
			foreach ($outputFiles AS $tempFile)
			{
				if ($tempFile != $fileName)
				{
					@unlink($tempFile);
				}
			}
			throw new XenForo_Exception(new XenForo_Phrase('image_could_be_processed_try_another_contact_owner'), true);
		}

		// done in 2 loops as multiple items may point to same file
		foreach ($outputFiles AS $sizeCode => $tempFile)
		{
			$this->_writeImage($contentId, $sizeCode, $originalImageType, $tempFile);
		}
		foreach ($outputFiles AS $tempFile)
		{
			if ($tempFile != $fileName)
			{
				@unlink($tempFile);
			}
		}
		$dwData = array(
			'image_date' => XenForo_Application::$time,
		);
		
		$dwData = array(
			'image_type' => $originalImageType,
		);

		$dw = XenForo_DataWriter::create('Brivium_Store_DataWriter_Product');
		$dw->setExistingData($contentId);
		$dw->bulkSet($dwData);
		$dw->save();

		return $dwData;
	}

	

	/**
	 * Writes out an image.
	 *
	 * @param integer $contentId
	 * @param string $imageType
	 * @param string $tempFile Temporary image file. Will be moved.
	 *
	 * @return boolean
	 */
	protected function _writeImage($contentId, $size, $imageType, $tempFile)
	{
		if (!in_array($size, array_keys(self::$_sizes)))
		{
			throw new XenForo_Exception('Invalid image size.');
		}
		
		$filePath = $this->getImageFilePath($contentId, $size, $imageType);
		$directory = dirname($filePath);

		if (XenForo_Helper_File::createDirectory($directory, true) && is_writable($directory))
		{
			if (file_exists($filePath))
			{
				unlink($filePath);
			}

			$success = rename($tempFile, $filePath);
			if ($success)
			{
				XenForo_Helper_File::makeWritableByFtpUser($filePath);
			}

			return $success;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Get the file path to an image.
	 *
	 * @param integer $contentId
	 * @param string $imageType
	 *
	 * @return string
	 */
	public function getImageFilePath($contentId, $size, $imageType)
	{
		return sprintf('%s/brsimages/products/%s/%d/%s%s',
			XenForo_Helper_File::getExternalDataPath(),
			$size,
			floor($contentId / 100),
			$contentId,
			$imageType
		);
	}

	/**
	 * Deletes a image.
	 *
	 * @param integer $contentId
	 * @param string $imageType
	 *
	 */
	public function deleteImage($contentId, $imageType)
	{
		foreach (array_keys(self::$_sizes) AS $size)
		{
			$filePath = $this->getImageFilePath($contentId, $size, $imageType);
			if (file_exists($filePath) && is_writable($filePath))
			{
				unlink($filePath);
			}
		}
	}
	
	/**
	 * Returns the _sizes array, defining what image sizes are available.
	 *
	 * @return array
	 */
	public static function getSizes()
	{
		return self::$_sizes;
	}

	/**
	 * Returns the maximum size (in pixels) of an image corresponding to the size code specified
	 *
	 * @param string $sizeCode (s,m,l)
	 *
	 * @return integer
	 */
	public static function getSizeFromCode($sizeCode)
	{
		return self::$_sizes[strtolower($sizeCode)];
	}
}