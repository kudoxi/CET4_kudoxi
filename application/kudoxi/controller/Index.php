<?php
namespace app\kudoxi\controller;

use app\common\controller\Frontend;
use Think\Db;
use think\Session;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use app\common\model\Mywords;
require_once $_SERVER['DOCUMENT_ROOT'].'/kudoxi/extend/PHPMailer/src/Exception.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/kudoxi/extend/PHPMailer/src/PHPMailer.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/kudoxi/extend/PHPMailer/src/SMTP.php';
class Index extends Frontend
{
	protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();
        $this->special_name = \think\Config::get("SPECIAL_NAME");
        $this->mywords = new Mywords();
        //Session::set("ahan_username","");
        $ahan_username = Session::get("ahan_username");
        if($ahan_username != ""){
        	if(in_array($ahan_username,$this->special_name)){
        		$this->is_login = 2;
        	}else{
        		$this->is_login = 1;
        	}
        }else{
        	$this->is_login = 0;
        }
        //判断三次机会是否都已经看过了
    	$special_name = $this->special_name;
    	$need_wait = 0;
    	$mw = Db::name("mywords")->select();
    	if(empty($mw)){
    		$need_wait = 1;
    	}
    	foreach($special_name as $k=>$v){
    		$is_read = Db::name("mywords")->where("name = '$v' and is_read != 1")->find();
    		if(!empty($is_read)){
    			$need_wait = 1;
    		}
    	}
    	$this->need_wait = $need_wait;
    	//判断是否为手机
    	$this->isMobile = isMobile();
    	$this->view->assign("need_wait", $need_wait);
		$this->view->assign("is_login", $this->is_login);
    }

    public function index()
    {
    	if(!$this->isMobile){
    		return $this->view->fetch();
    	}else{
    		return $this->view->fetch("mobile_index");
    	}
    }
    public function chatbox(){
    	return $this->view->fetch();
    }
    //登陆判断
    public function checkname(){
    			
    	if($this->request->isPost()){
    		$name = $this->request->post("name");
    		Session::set("ahan_username",$name);
    		
    		//是阿韩
    		if(in_array($name,$this->special_name)){
    			//判断是否已经看过
    			$mywords = Db::name("mywords")->where("name",$name)->find();
    			//取随机数
    			$bye = \think\Config::get("BYE");
    			$arr = range(1,count($bye));
    			shuffle($arr);
    			$rang_num = $arr[0];
    			//看过且已经拒绝
    			if($mywords['is_read'] == 1){
    				return array("0"=>"101","1"=>$bye[$rang_num]);
    			}elseif($mywords['is_read'] == 2){
    				//看过且已经保存
    				return array("0"=>"102","1"=>"东西放在你信箱了哦");
    			}else{
    				//第一次看
    				if(!$mywords){
    					$data = array("is_read"=>0,"name"=>$name,"ctime"=>date('Y-m-d H:i:s'));
	    				$success = Db::name("mywords")->insert($data);
    				}else{
    					$success = 1;
    				}
	    			if($success){
	    				send_email($name."找到信了",$name."找到信了","1140514109@qq.com");
	    				return array("0"=>"100","1"=>"找到你了");
	    			}else{
	    				return array("0"=>"104","1"=>"你是不是网不好啊");
	    			}
    			}
    		}else{
    			if($this->need_wait == 0){
	    			return array("0"=>"103","1"=>"好，进去吧");
	    		}
    			//是路人甲
    			return array("0"=>"103","1"=>"不是...进去学习吧，我再等等");
    		}
    	}
    }
    //设置看或不看的session
    public function lookornot(){
    	if($this->request->isPost()){
    		$look = $this->request->post("look");
    		Session::set("LOOK",$look);
    	}else{
    		$ahan_username = Session::get("ahan_username");
    		if($ahan_username != ""){
    			$s_look = Session::get("LOOK");
    			$look = $s_look != "" ? $s_look : 0;
    			return array("0"=>"101","1"=>$look);
    		}else{
    			return array("0"=>"102","1"=>"你还没回答我呢...");
    		}
    	}
    }
    //退出登录
    public function login_out(){
    	Session::set("ahan_username","");
    	$this->success("再见",url("kudoxi/index/index"));
    }
}
?>