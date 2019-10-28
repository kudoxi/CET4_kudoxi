<?php
namespace app\kudoxi\controller;

use app\kudoxi\controller\Base;
use Think\Db;
use think\Session;
class Vocabulary extends Base
{
	public function _initialize()
    {
    	parent::_initialize();	
    }
    public function vocabulary(){
    	$lid = input('lid');
		$name = $this->request->get('name');
		$lid2 = $this->request->get('lid');
		$lid = $lid ? $lid : $lid2;
    	//默认是冰岛语
    	$moren_lid['id'] = 0;
    	$moren_lid['name'] = "Iceland";
    	$moren_lid = Db::name("language")->where("is_shenpi",1)->order("id asc")->find();
    	$lid = $lid ? $lid : $moren_lid['id'];
    	$moren_lid2 = Db::name("language")->where("id = $lid and is_shenpi = 1")->find();
    	$moren = !empty($moren_lid2) ? $moren_lid2['name'] : $moren_lid['name'];
    	$list = array();
    	if($lid){
			$where = "language = $lid";
			if($name != ''){
				$where .= " and ( name like '%" . $name . "%' or remark like '%" . $name . "%')";
			}
    		$list = model("vocabulary")->where($where)->order("zan DESC,id DESC")->paginate(20);
    		//计算点赞量
    		foreach($list as $k => $v){
    			$id = $v['id'];
    			$res = $this->count_zan_percent("vocabulary",$id);
    			$list[$k]['ok'] = $res['ok'];
    			$list[$k]['percent'] = $res['percent'];
    			$lname = Db::name("language")->where("id = ".$v['language'])->find();
    			if(in_array($lname['name'],$this->file_type)){
    				$list[$k]['is_file'] = 1;
    			}else{
    				$list[$k]['is_file'] = 0;
    			}
    		}
    	}
		
    	$this->view->assign("name",$name);
    	$this->view->assign("lid",$lid);
    	$this->view->assign("language",$moren);
    	$this->view->assign("list",$list);
    	return $this->view->fetch();
    }
}
?>