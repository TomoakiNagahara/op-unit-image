op-unit-image
===

# How to use
```
<?php
/* @var $this App */
$this->mark();

/* @var $$model_image Model_Image */
$model_image = $this->Unit('Image');
$model_image->Test();
$model_image->FormDisplay();

$this->mark( $model_image->FormSecure() );
$this->mark( $model_image->FormStatus() );

$model_image = $this->Unit('Image');
if( $file_path  = $model_image->GetFilePath() ){
	$image_orig = $model_image->GetImage($file_path);
	$image_copy = $model_image->GetImageResize($image_orig, 128, 128);
	$model_image->SaveImage($image_copy, 'app:/upload/resize.jpg');
	imagedestroy($image_orig);
	imagedestroy($image_copy);
	unlink($file_path);
}

?>
<html>
<p><?= $model_image->GetError() ?></p>
<img src="/upload/resize.jpg" />
<img src="<?= $file ?>"/>
</html>
```

# php.ini
post_max_size
upload_max_filesize
