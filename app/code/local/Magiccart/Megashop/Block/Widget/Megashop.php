<?php
/**
 * Magiccart 
 * @category 	Magiccart 
 * @copyright 	Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license 	http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2014-04-14 15:31:56
 * @@Modify Date: 2015-12-08 15:10:23
 * @@Function:
 */
?>
<?php
class Magiccart_Megashop_Block_Widget_Megashop extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{

    protected function _construct()
    {
        $path = 'megashop/identifier/' . $this->getIdentifier();
        $data = unserialize(Mage::getStoreConfig($path));
        $this->addData($data);
        parent::_construct();
    }
    
    public function getGeneralCfg($cfg)
    {
        return Mage::helper('megashop')->getGeneralCfg($cfg);
    }

    public function getProductCfg()
    {

        $options = array('limit', 'productDelay', 'widthImages', 'heightImages', 'timer', 'action', 'category_id');
        $ajax = array();
        foreach ($options as $option) {
            $ajax[$option] = $this->getData($option);
        }
        return $ajax;
    }

    public function getTabActive()
    {
        $active = $this->getActive(); // get form Widget
        $tabs = $this->getTabs();
        $types = array_keys($tabs);
        if(!in_array($active, $types)){
            $active = $types[0];            
        }
        return $active;
    }

    public function getContentActive($template)
    {
        return $this->getLayout()
               ->createBlock('megashop/product_grid')
               ->setActive($this->getTabActive()) //or ->setData('active', $this->getTabActive())
               ->setCfg($this->getData())
               ->setTemplate($template)
               ->toHtml();
    }

	public function getCatName()
	{
		$cat = Mage::getModel('catalog/category')->load($this->getData('category_id'));
		if($cat) return $cat->getName();
	}
	
    public function getTabs()
    {
        $cfg = $this->getTypes();
        // $cfg = explode(',', $cfg);
        $tabs = array();
        $types = Mage::getSingleton("megashop/system_config_type")->toOptionArray();
        foreach ($types as $type) {
            if(in_array($type['value'], $cfg)) $tabs[$type['value']] = $type['label'];
        }

        return $tabs;
    }

    public function getImages(){

        $gallery = $this->getData('megashop_tabs');
        if(isset($gallery['images']) && !empty($gallery['images'])){
            $options = json_decode($gallery['images'], true);
            $temp = array();
            foreach ($options as $key => $opt) {
                if($opt['disabled']){
                    unset($options[$key]);
                    continue;
                }
                $opt['url'] = Mage::helper('megashop')->getBaseTmpMediaUrl() .$opt['file'];
                $temp[$key] = $opt['position'];
            }
            array_multisort($temp, SORT_ASC, $options);
        }
        return $options;
    }

    public function getRelatedTabs()
    {

        $cfg = $this->getCategoryIds();
        $categoryIds = explode(',', $cfg);
        $tabs =  Mage::getModel('catalog/category')->getCollection()
                        ->addAttributeToFilter('entity_id', array('in' => $categoryIds))
                        ->addAttributeToSelect('magic_thumbnail')
                        ->addAttributeToSelect('name');
        if(!count($tabs)){
            $_category = Mage::getModel('catalog/category')->load($this->getCategoryId());

            $tabs = $_category->getChildren();
        }
        return $tabs;

    }

    public function getThumbnail($object)
    {
        if($file = $object->getMagicThumbnail()){
            $image  = Mage::helper('megashop/image');
            $width  = 50; //$this->config['widthThumb'];
            $height = 50; //$this->config['heightThumb'];
            $img    = $image->resizeImg('/'.$file);
            if($img) return '<img class="img-responsive" alt="' .$object->getName(). '" src="'.$img.'">';
        }
    }

    public function getDevices()
    {
        $devices = array('portrait'=>480, 'landscape'=>640, 'tablet'=>768, 'desktop'=>992);
        return $devices;
    }

    public function getItemsDevice()
    {
        $screens = $this->getDevices();
        $screens['visibleItems']  = 993;
        $itemOnDevice = array();
        // $itemOnDevice['320'] = '1';
        foreach ($screens as $screen => $size) {
            // $fn = 'get'.ucfirst($screen);
            // $itemOnDevice[$size] = $this->{$fn}();
            $itemOnDevice[$size] = $this->getData($screen);
        }
        return $itemOnDevice;
    }

    public function setFlexiselArray()
    {
        
        //var_dump($this->getData());die;
        if($this->getData('slide')){
            $optTrue = array(
                // 'infiniteLoop',
                'pager',
                'controls',
            );
            $options = array(
                'speed',
                'auto',
                'pause',
                'visibleItems',
            );
            $script = array();
            $script['moveSlides'] = 2;
            $script['infiniteLoop'] = 0;
            $script['maxSlides'] = $this->getData('visibleItems');
            $script['slideWidth'] = $this->getData('widthImages');
            if($this->getData('vertical')){
                $script['mode'] = 'vertical';
                $script['minSlides'] = $this->getData('visibleItems');
            }
            if($this->getData('marginColumn')) $script['slideMargin'] = (int) $this->getData('marginColumn');
            foreach ($optTrue as $opt) {
                $script[$opt] = $this->getData($opt) ? 1 : 0;
            }
            foreach ($options as $opt) {
                $cfg = $this->getData($opt);
                if($cfg) $script[$opt] = (int) $cfg;
            }
            $responsiveBreakpoints = $this->getDevices();
            // $script['responsiveBreakpoints']['mobile'] = array('changePoint'=> 320, 'visibleItems'=> 1);
            foreach ($responsiveBreakpoints as $opt => $screen) {
                $cfg = $this->getData($opt);
                if($cfg) $script['responsiveBreakpoints'][(int) $screen] = (int) $cfg;
                if($cfg > $script['maxSlides']) $script['maxSlides'] = $cfg;
            }
            return $script;
        }
    }

    public function generateRandomString($length = 10) {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
	
}
