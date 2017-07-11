<?php

class KomuKu_ProfileCover_XenForo_Image_Gd extends XFCP_KomuKu_ProfileCover_XenForo_Image_Gd
{
	public function resize($width, $height)
	{
		$newImage = imagecreatetruecolor($width, $height);
		$this->_preallocateBackground($newImage);

		imagecopyresized($newImage, $this->_image,
			0, 0, 0, 0,
			$width, $height, $this->_width, $this->_height
		);

		$this->_setImage($newImage);

		return true;
	}
}