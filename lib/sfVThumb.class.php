<?php
require_once ('class.upload.php');

class sfVThumb
{
	
	private $nameConfigFile = 'Vrules';
	
	private $pathConfigFile = '';
	
	private $resizeNums = null;
	
	private $resizeNum = 0;
	
	private $settingsArray = array();
	
	private $resizer;
	
	private $saveAs = '';
	
	private $saveAsBase = '';
	
	private $savePath = '';
	
	private $params = array(
		'filename' => array(
			'name' => 'file_new_name_body'
		),
		'resize' => array(
			'name' => 'image_resize', 
			'oneparam' => 'true',
			'params' => array(
				'true' => 'true'
			)
		),
		'grey' => array(
			'name' => 'image_greyscale',
			'oneparam' => 'true', 
			'params' => array(
				'true' => 'true'
			)
		),
		'background' => array(
			'name' => 'image_background_color'
		),
		'label_text' => array(
			'name' => 'image_text'
		),
		'label_direction' => array(
			'name' => 'image_text_direction',
			'oneparam' => 'true',
			'params' => array(
				'horisontal' => 'h',
				'vertical' => 'v'
			)
		),
		'label_text_background' => array(
			'name' => 'image_text_background'
		),
		'label_text_opacity' => array(
			'name' => 'image_text_background_percent'
		),
		'label_position' => array(
			'name' => 'image_text_position',
			'params' => array(
				'left' => 'L',
				'right' => 'R',
				'top' => 'T',
				'bottom' => 'B',
			)
		),
		'label_position_x' => array(
			'name' => 'image_text_x'
		),
		'label_position_y' => array(
			'name' => 'image_text_y'
		),
		'reflection_height' => array(
			'name' => 'image_reflection_height'
		),
		'reflection_opacity' => array(
			'name' => 'image_reflection_opacity'
		),
		'watermark' => array(
			'name' => 'image_watermark '
		),
		'watermark_position' => array(
			'name' => 'image_watermark_position',
			'params' => array(
				'left' => 'L',
				'right' => 'R',
				'top' => 'T',
				'bottom' => 'B',
			)
		),
		'watermark_x' => array(
			'name' => 'image_watermark_x'
		),
		'watermark_y' => array(
			'name' => 'image_watermark_y'
		),
		'format' => array(
			'name' => 'image_convert',
			'oneparam' => 'true',
			'params' => array(
				'png' => 'png',
				'jpeg' => 'jpeg',
				'gif' => 'gif',
			)
		),
		'keep_ratio' => array(
			'name' => 'image_ratio',
			'oneparam' => 'true', 
			'params' => array(
				'true' => 'true'
			)
		),
		'ratio_fill' => array(
			'name' => 'image_ratio_fill',
			'params' => array(
				'true' => 'true',
				'left' => 'L',
				'right' => 'R',
				'top' => 'T',
				'bottom' => 'B',
			)
		),
		'rotate' => array(
			'name' => 'image_rotate',
			'oneparam' => 'true',
			'params' => array(
				'90' => '90',
				'180' => '180',
				'270' => '270',
			)
		),
		'flip' => array(
			'name' => 'image_flip',
			'oneparam' => 'true',
			'params' => array(
				'horisontal' => 'h',
				'vertical' => 'v'
			)
		),
		'width' => array(
			'name' => 'image_x'
		),
		'height' => array(
			'name' => 'image_y'
		),
	
	);
	
	public function __construct( $file, $rulename, $config = '')
	{
		$this->pathConfigFile = $this->getConfig( $config );
		$this->parseConfig( $rulename );
		$this->resizer = new upload( $file );
		$t = pathinfo($file);
		$this->saveAsBase = $t['filename'];
		$this->savePath = $t['dirname'];
	}

	public function doResize()
	{
		//$r = array();
		for( $i = $this->resizeNum; $i < $this->resizeNums; $i++ )
		{
			$this->resizer->init();
			$this->translateParams();
			$this->translateParams($i);
			$this->resizer->file_new_name_body = $this->saveAs;
			$this->resizer->process($this->savePath);
			//$r[] = $this->resizer->log;
			if ($this->resizer->processed and $this->settingsArray['delete_source'] == 'true')
				$this->resizer->clean();
		}
		//return $r;
	}
	
	private function translateParams( $n = null )
	{
		if( is_null( $n ) )
		{
			$config = $this->settingsArray['params'];
		} else
		{
			$config = $this->settingsArray['sizes'][$n]['params'];
			if ( isset($config['filename']) )
				$this->saveAs = $config['filename'] . $this->saveAsBase;
			else
				$this->saveAs = $n . $this->saveAsBase;
		}
		foreach( $config as $key => $value )
		{
			$outParam = $this->params[$key]['name'];
			$outValue = $value;
			if ( isset($this->params[$key]['params']) and isset( $this->params[$key]['oneparam'] ) )
			{
				$values = explode(' ',$value);
				$outValue = '';
				foreach( $values as $vKey => $vValue )
				{
					$outValue .= trim( $vValue );
				}
			}
			$this->resizer->{$outParam} = $outValue;
		}
	}
	
	private function parseConfig( $rule )
	{
		$yaml = sfYaml::load( $this->pathConfigFile );
		$this->settingsArray = $yaml['vThumb'][$rule];
		$this->resizeNums = count( $this->settingsArray['sizes'] );
	}
	
	
	
	private function getConfig( $name )
	{
		if( empty( $name ) )
			$name = $this->nameConfigFile . '.yml';
		else
			$name = $name . '.yml';
			
		$ymlVa = sfConfig::get('sf_vthumb_config') . DIRECTORY_SEPARATOR . $name;
		$ymlVb = sfConfig::get('sf_app_config_dir') . DIRECTORY_SEPARATOR . $name;
			
		if( file_exists( $ymlVa ) )
			$name = $ymlVa;
		elseif( file_exists( $ymlVb ) )
			$name = $ymlVb;
		
		return $name;
	}
	
	
	
}