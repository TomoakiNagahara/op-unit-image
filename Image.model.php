<?php
/**
 * op-unit-image/Image.model.php
 *
 * @creation  2016-05-11
 * @version   1.0
 * @package   op-unit-image
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/**
 * Model_Image
 *
 * @creation  2016-05-11
 * @version   1.0
 * @package   op-unit-image
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Model_Image extends OnePiece5
{
	/**
	 * @var string
	 */
	private $_error;

	/**
	 * @var Model_Image_Form
	 */
	private $_form;

	function __destruct()
	{
		if( $this->_form ){
			$this->_form = null;
		}
		parent::__destruct();
	}

	private function _form()
	{
		if(!$this->_form){
			$this->_form = new Model_Image_Form();
		}
		return $this->_form;
	}

	function FormDisplay()
	{
		$this->_form()->Display();
	}

	function FormFile()
	{
		$form_name = $this->_form()->GetFormName();
		return $this->_form()->GetInputValue('file', $form_name);
	}

	function FormSecure()
	{
		$form_name = $this->_form()->GetFormName();
		return $this->_form()->Secure($form_name);
	}

	function FormStatus()
	{
		$form_name = $this->_form()->GetFormName();
		return $this->_form()->GetStatus($form_name);
	}

	function GetError()
	{
		return $this->_error;
	}

	/**
	 * Get GD image. (resource)
	 *
	 * @param  $path
	 * @return boolean|resource
	 */
	function GetImage($path)
	{
		//	Full path
		$full_path = $this->ConvertPath("app:/$path");
		if(!file_exists($full_path) ){
			$this->_error = "Does not exists file. ($full_path)";
			return false;
		}

		//	Image info
		$image_info = getimagesize($full_path);

		//	Get image
		switch($image_mime = $image_info['mime']){
			case 'image/jpeg':
				$image = imagecreatefromjpeg($full_path);
				break;
			case 'image/png':
				$image = imagecreatefrompng($full_path);
				break;
			case 'image/gif':
				$image = imagecreatefromgif($full_path);
				break;
			default:
				$this->_error = "Does not support this image type. ($image_mime)";
		}

		return $image;
	}

	function GetImageResize($image_orig, $width, $height, $position=null)
	{
		$image = imagecreatetruecolor($width, $height);
		if( $alpha = imagecolortransparent($image_orig) ){
			imagefill($image, 0, 0, $alpha);
			imagecolortransparent($image, $alpha);
		}else{
			imagealphablending($image, false);
			imagesavealpha($image, true);
		}

		ImageCopyResampled($image, $image_orig, 0, 0, 0, 0, $width, $height, imagesx($image_orig), imagesy($image_orig));

		return $image;
	}

	function SaveImage($image, $path)
	{
		if(!preg_match('/\.([a-z]+)$/', $path, $m) ){
			$this->_error = "Does not match extension. ($path)";
			return false;
		}

		$full_path = $this->ConvertPath($path);
		if(!is_dir( dirname($full_path) ) ){
			$this->_error = "Does not exists this directory. ($full_path)";
			return false;
		}

		switch( $extension = $m[1] ){
			case 'jpg':
				$io = imagejpeg($image, $full_path, 90);
				break;
			case 'png':
				$io = imagepng($image, $full_path, 1);
				break;
			case 'gif':
				$io = imagegif($image, $full_path);
				break;
			default:
				$this->_error = "Does not support this extension. ($extension)";
		}

		return $io;
	}

	function Test()
	{
		$this->mark(__FILE__);
	}
}

/**
 * Model_Image_Form
 *
 * @creation  2016-05-11
 * @version   1.0
 * @package   op-unit-image
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Model_Image_Form extends Form5
{
	private function _Config()
	{
		$form = new Config();
		$form->name = $this->GetFormName();

		$input = new Config();
		$input->name  = 'file';
		$input->type  = 'file';
		$input->save->dir = 'app:/upload/';
		$input->validate->required = true;
		$input->validate->permit   = 'image';
		$input->error->file  = 'ファイルの送信に失敗しました。($value)';
		$input->error->size  = 'ファイルサイズが上限を超えました。($value)';
		$input->error->image = 'ファイルがイメージではありません。';
		$form->input->{$input->name} = $input;

		$input = new Config();
		$input->name  = 'MAX_FILE_SIZE';
		$input->type  = 'hidden';
		$input->value = 20 * 1024 * 1024; // 20MB
		$form->input->{$input->name} = $input;

		$input = new Config();
		$input->name  = 'submit';
		$input->type  = 'submit';
		$input->value = ' Submit ';
		$form->input->{$input->name} = $input;

		return $form;
	}

	function Display()
	{
		$form_name = $this->GetFormName();
		$this->Start($form_name);
		$this->Input('size');
		$this->Input('file');
		$this->Error('file');
		$this->Input('submit');
		$this->Finish($form_name);
	//	$this->Debug($form_name);
	}

	function GetFormName()
	{
		return md5(__FILE__);
	}

	function Init()
	{
		parent::Init();
		$this->AddForm($this->_Config());
	}
}