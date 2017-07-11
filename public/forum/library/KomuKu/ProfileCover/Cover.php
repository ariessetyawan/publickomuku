<?php
class KomuKu_ProfileCover_Cover
{
	protected $_width = 1024;
	protected $_height = 350;

	protected $_upload;

	protected $_controller;

	protected $_crops;

	protected $_tempFile;

	protected $_result = array();

	public function __construct(XenForo_Upload $upload = null, XenForo_Controller $controller, array $crops)
	{
		$this->_upload = $upload;
		$this->_controller = $controller;

		$this->_crops = $crops;

		$dimensions = XenForo_Application::getOptions()->KomuKu_ProfileCover_dimensions;

		$this->_width = empty($dimensions['width']) ? 1024 : $dimensions['width'];
		$this->_height = empty($dimensions['height']) ? 350 : $dimensions['height'];
	}

	public function doCrop()
	{
		if ($this->_upload instanceof XenForo_Upload)
		{
			$this->_crop();
		}
		else
		{
			$this->_reCrop();
		}
	}

	public function getResult()
	{
		return $this->_result;
	}

	protected function _reCrop()
	{
		$user = XenForo_Visitor::getInstance()->toArray();

		$filePath = static::getFilePath($user['user_id'], true);
		$output = static::getFilePath($user['user_id']);

		if (! file_exists($filePath))
		{
			throw new XenForo_Exception(sprintf("User %s did not have an custom cover.", $user['username']));
		}
		@unlink($output);

		$crops = $this->_crops;
		$imageinfo = getimagesize($filePath);

		if (! $imageinfo)
		{
			$this->_controller->responseException(
				$this->responseError(new XenForo_Phrase('profile_cover_oops_something_went_wrong'))
			);

			return;
		}

		$image = XenForo_Image_Gd::createFromFileDirect($filePath, $imageinfo[2]);
		$width = isset($crops['containerW']) ? $crops['containerW'] : $this->_width;

		if ($width < $this->_width)
		{
			$width = $this->_width;
		}
		$ratio = $width / $image->getWidth();

		$resizeHeight = ceil($ratio * $image->getHeight());

		$image->resize($width, $resizeHeight);
		$image->crop(0, ceil($crops['cropY'] * $ratio), $width, $this->_height);
		$image->output(IMAGETYPE_JPEG, $output, KomuKu_ProfileCover_Option::get('quality'));
		
		$user['cover_date'] = XenForo_Application::$time;
		$this->_result['cover'] = static::helperUserCover($user);
	}

	protected function _crop()
	{
		$tempFile = tempnam(XenForo_Helper_File::getTempDir(), 'xf');
		$success = move_uploaded_file($this->_upload->getTempFile(), $tempFile);

		if (! $success)
		{
			@unlink($tempFile);

			$this->_controller->responseException(
				$this->responseError(new XenForo_Phrase('profile_cover_oops_something_went_wrong'))
			);

			return;
		}

		$backup = static::getFilePath(XenForo_Visitor::getUserId(), true);
		if (file_exists($backup))
		{
			@unlink($backup);
		}
		$directory = dirname($backup);

		$canBackup = XenForo_Helper_File::createDirectory($directory, true) && is_writable($directory);

		if ($canBackup)
		{
			XenForo_Helper_File::safeRename($tempFile, $backup);
		}
		else
		{
			@unlink($tempFile);

			$this->_controller->responseException(
				$this->responseError(new XenForo_Phrase('profile_cover_oops_something_went_wrong'))
			);

			return;
		}

		$image = XenForo_Image_Gd::createFromFileDirect($backup, $this->_upload->getImageInfoField('type'));

		$ratio = $this->_width / $image->getWidth();

		$image->resize($this->_width, $ratio * $image->getHeight());
		$image->crop(0, 0, $this->_width, $this->_height);

		$visitor = XenForo_Visitor::getInstance();

		$output = static::getFilePath($visitor['user_id']);
		$directory = dirname($output);
		$canWrite = XenForo_Helper_File::createDirectory($directory, true) && is_writable($directory);

		if (! $canWrite)
		{
			@unlink($tempFile);
			@unlink($backup);

			$this->_controller->responseException(
				$this->responseError(new XenForo_Phrase('profile_cover_oops_something_went_wrong'))
			);

			return;
		}

		$image->output(IMAGETYPE_JPEG, $output, KomuKu_ProfileCover_Option::get('quality'));

		$this->_result['crop'] = static::helperUserCover($visitor->toArray());
	}

	/** Static Helper Methods **/

	public static function getFilePath($userId, $fullsize = false, $externalDataPath = null)
	{
		if ($externalDataPath === null)
		{
			$externalDataPath = XenForo_Helper_File::getExternalDataPath();
		}

		return sprintf('%s/covers%s/%d/%d.jpg',
			$externalDataPath,
			($fullsize ? '/full' : ''),
			floor($userId / 1000),
			$userId
		);
	}

	public static function helperUserCover(array $user, $isSource = false)
	{
		if (empty($user['user_id']) OR empty($user['cover_date']))
		{
			return KomuKu_ProfileCover_Option::get('default');
		}

		return sprintf('%s/covers%s/%d/%d.jpg?t=%d',
			XenForo_Application::$externalDataUrl,
			($isSource ? '/full' : ''),
			floor($user['user_id'] / 1000),
			$user['user_id'],
			$user['cover_date']
		);
	}

	public static function deleteCover($userId)
	{
		$source = static::getFilePath($userId, true);
		if (file_exists($source))
		{
			@unlink($source);
		}

		$crop = static::getFilePath($userId);
		if (file_exists($crop))
		{
			@unlink($crop);
		}
	}

	public static function assertCanUploadCover()
	{
		$visitor = XenForo_Visitor::getInstance();
		if (! $visitor)
		{
			return false;
		}

		$filesize = $visitor->hasPermission('general', 'cover_maxFilesize');
		$canUpload = $visitor->hasPermission('general', 'cover_upload');

		return ($canUpload && $filesize);
	}

	/** NewsFeed handler **/
	const PROFILE_COVER = 'profile_cover';
	const UPDATE_ACTION = 'update';

	public static function publishNewsFeed(XenForo_DataWriter_User $dw)
	{
		if ($dw->get('cover_date'))
		{
			// Delete Previous Item
			static::deleteNewsFeed($dw);

			$dw->getModelFromCache('XenForo_Model_NewsFeed')->publish(
				$dw->get('user_id'),
				$dw->get('username'),
				static::PROFILE_COVER,
				$dw->get('user_id'),
				static::UPDATE_ACTION
			);
		}
		else
		{
			static::deleteNewsFeed($dw);
		}
	}

	public static function deleteNewsFeed(XenForo_DataWriter_User $dw)
	{
		$dw->getModelFromCache('XenForo_Model_NewsFeed')->delete(static::PROFILE_COVER, $dw->get('user_id'));
	}

}



