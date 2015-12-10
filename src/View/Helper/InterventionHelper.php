<?php
namespace Media\View\Helper;

use Cake\View\Helper;
use Cake\View\StringTemplateTrait;
use Intervention\Image\ImageManager;

class InterventionHelper extends Helper
{
	public $helpers = ['Html'];
	public $manager;
	public $defaultType = 'ratio';
	public $destination;
	public $format;

	public function __construct($config = []){
		parent::__construct($config);
		$this->manager = new ImageManager(array('driver' => 'gd'));
	}

	public function image($path , $width , $height , $type, $options = array()){
		
		$img = $this->Html->image($path, $options);
		return $img;
	}

	public function resize($path , $width , $height , $type=null, $force=null , $options = array()){
		$this->width = $width;
		$this->height = $height;
		if (!$type) {
			$type = $this->defaultType;
		}
		$pathinfo = pathinfo($path);
		$dest = $pathinfo['dirname'].'/'.$pathinfo['filename'].'_'.(int)$width.'x'.(int)$height.'.'.$pathinfo['extension'];

		$absolutDest = WWW_ROOT.$pathinfo['dirname'].'/'.$pathinfo['filename'].'_'.(int)$width.'x'.(int)$height.'.'.$pathinfo['extension'];

		$this->destination = $dest;
		$absolutPath = WWW_ROOT.ltrim($path, '/');
		$img = $this->manager->make($absolutPath);
		if (empty(glob($absolutDest)) || $force) {
			//die('lol');
			if ($type == 'ratio'){
				$img = $this->resizeRatio($img);
			}elseif($type == 'crop'){
				$img = $this->resizeCrop();
			}
			$img->save($absolutDest);
		}

		$img = $this->Html->image($dest, $options);
		return $img;
		
	}

	private function getFormat($img){
		if ($img->height()>$img->width()) {
			$this->format = "portrait";
		}elseif($img->height()==$img->width()){
			$this->format = "carre";
		}else{
			$this->format = "paysage";
		}
		return $this->format;
	}

	public function resizeCrop($x=null , $y=null){

	}

	private function resizeRatio($img){
		$format = $this->getFormat($img);
		if ($format == 'portrait') {
			$img = $img->resize(null, $this->height, function($constraint){
				$constraint->aspectRatio();
			});
		}else{
			$img = $img->resize($this->width, null , function($constraint){
				$constraint->aspectRatio();
			});

		}
		return $img;

		
	}

}