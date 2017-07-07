<?php
/**
 * Created by PhpStorm.
 * User: xhm
 * Date: 2017/7/7
 * Time: 14:48
 */

class AuthCode{

    protected $im = null;
    //背景图片
    protected $im_fullbg = null;
    //固定大小背景
    protected $im_bg = null;
    //拖拽图片上下偏移黑色背景
    protected $im_slide = null;
    //生成验证背景图片大小
    protected $bg_width = 240;
    protected $bg_height = 150;
    //生成验证拖拽图片大小
    protected $mark_width = 50;
    protected $mark_height = 50;
    //背景图片数量
    protected $bg_num = 6;
    protected $_x = 0;
    protected $_y = 0;
    //容错象素 越大体验越好，越小破解难道越高
    protected $_fault = 3;

    public function __construct()
    {
        if(!isset($_SESSION)){
            session_start();
        }
    }

    /**
     * 初始化，生成需要的图片资源
     */
    private function _init(){
        //随机选择背景图片
        $bg = mt_rand(1,$this->bg_num);
        $file_bg = dirname(__FILE__).'/bg/'.$bg.'.png';
        //根据背景图片创建一个新图片
        $this->im_fullbg = imagecreatefrompng($file_bg);
        //创建一个固定大小黑色背景图
        $this->im_bg = imagecreatetruecolor($this->bg_width, $this->bg_height);
        //将 im_fullbg 图像中坐标从0,0开始，宽度为 bg_width，高度为 bg_height 的一部分拷贝到 im_bg 图像中坐标为 0 和 0 的位置上。
        imagecopy($this->im_bg,$this->im_fullbg,0,0,0,0,$this->bg_width, $this->bg_height);
        //生成拖拽图片上下偏移的黑色背景图
        $this->im_slide = imagecreatetruecolor($this->mark_width, $this->bg_height);
        //随机偏移量并保存在session，方便提交验证
        $_SESSION['auth_width'] = $this->_x = mt_rand(50,$this->bg_width-$this->mark_width-1);
//        $_REQUEST['tn_c'] = 0;
        $this->_y = mt_rand(0,$this->bg_height-$this->mark_height-1);
    }
    /**
     * 创建拖拽图片
     */
    private function _createSlide(){
        //拖拽图片
        $file_mark = dirname(__FILE__).'/img/mark.png';
        $img_mark = imagecreatefrompng($file_mark);
        //将拖拽图片从背景图片切出来
        imagecopy($this->im_slide, $this->im_fullbg,0, $this->_y , $this->_x, $this->_y, $this->mark_width, $this->mark_height);
        imagecopy($this->im_slide, $img_mark,0, $this->_y , 0, 0, $this->mark_width, $this->mark_height);
        imagecolortransparent($this->im_slide,0);//16777215
        //header('Content-Type: image/png');
        //imagepng($this->im_slide);exit;
        imagedestroy($img_mark);
    }
    /**
     * 创建背景图片
     */
    private function _createBg(){
        $file_mark = dirname(__FILE__).'/img/mark.png';
        $im = imagecreatefrompng($file_mark);
//        header('Content-Type: image/png');
        //imagealphablending( $im, true);
        imagecolortransparent($im,0);//16777215
        //imagepng($im);exit;
        imagecopy($this->im_bg, $im, $this->_x, $this->_y  , 0  , 0 , $this->mark_width, $this->mark_height);
        imagedestroy($im);
    }

    /**
     * 合并图片
     */
    private function _merge(){
        $this->im = imagecreatetruecolor($this->bg_width, $this->bg_height*3);
        imagecopy($this->im, $this->im_bg,0, 0 , 0, 0, $this->bg_width, $this->bg_height);
        imagecopy($this->im, $this->im_slide,0, $this->bg_height , 0, 0, $this->mark_width, $this->bg_height);
        imagecopy($this->im, $this->im_fullbg,0, $this->bg_height*2 , 0, 0, $this->bg_width, $this->bg_height);
        imagecolortransparent($this->im,0);//16777215
//        header('Content-Type: image/png');
//        imagepng($this->im);exit;
    }

    /**
     * 输出图片流
     */
    private function _imgout(){
        if(!$_GET['nowebp']&&function_exists('imagewebp')){//优先webp格式，超高压缩率
            $type = 'webp';
            $quality = 40;//图片质量 0-100
        }else{
            $type = 'png';
            $quality = 7;//图片质量 0-9
        }
        header('Content-Type: image/'.$type);
        $func = "image".$type;
        $func($this->im,null,$quality);
    }

    /**
     * 销毁图像
     */
    private function _destroy(){
        imagedestroy($this->im);
        imagedestroy($this->im_fullbg);
        imagedestroy($this->im_bg);
        imagedestroy($this->im_slide);
    }

    /**
     * 获取图片信息
     */
    public function make(){
        $this->_init();
        $this->_createSlide();
        $this->_createBg();
        $this->_merge();
        $this->_imgout();
        $this->_destroy();
    }

    /**
     * 判断是否成功
     */
    public function check($offset=''){
        if(!$offset){
            $offset = $_REQUEST['width'];
        }
//        $_REQUEST['tn_c']++;
//        if($_REQUEST['tn_c']>1){
//            $_REQUEST['tn_r'] = null;
//        }
        //echo $_SESSION['tncode_r']."|".$offset;
        return abs($_SESSION['auth_width']-$offset)<=$this->_fault;
    }
}