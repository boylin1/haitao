<?php
/**
 * 
 * FormatAction.class.php (格式化输出)
 *
 * @package      	QQCMS
 * @author          Ivan QQ:79441928 <admin@qqcms.net>
 * @copyright     	Copyright (c) 2008-2011  (http://www.qqcms.net)
 * @license         http://www.qqcms.net/license.txt
 * @version        	QQCMS网站管理系统 v4.1.5 2011-03-01 qqcms.net $
 */
if(!defined("QQCMS")) exit("Access Denied"); 
class FormatAction extends BaseAction
{

	function _initialize()
    {	
		parent::_initialize();
		import("@.ORG.Cxml");
    }

    public function index()
    {
		 
    }
	public function rss()
	{
		$modle = M('Article');
		$data = $modle->field('id,title,url,createtime,copyfrom,content')->where("status=1")->order('id desc')->limit('0,10')->select();

 
		$arraya['title']['value']=$this->Config['site_name'];
		$arraya['link']['value']=$this->Config['site_url'];
		$arraya['description']['value']=$this->Config['seo_title'];
		$arraya['generator']['value']='QQCMS'.VERSION;
		$arraya['lastBuildDate']['value']= gmdate('D, d M Y H:i:s \G\M\T',time()+ 3600 * 8);
		$arraya['webMaster']['value']= $this->Config['site_email'];
		$arraya['language']['value']= 'zh-cn';
		foreach($data as $key=> $res){
			//$arraya[$key]['NodeName']['attributes']=array('id'=>'3','class'=>'thue');
			$arraya[$key]['NodeName']['value'] ='item';
			$arraya[$key]['title']['value'] = $res['title'];
			$arraya[$key]['link']['value'] = $this->Config['site_url'].$res['url'];
			$arraya[$key]['description']['value'] = $res['content'];
			$arraya[$key]['description']['ishtml']=1;
			$arraya[$key]['pubDate']['value'] = gmdate('D, d M Y H:i:s \G\M\T',$res['createtime']+ 3600 * 8);
			$arraya[$key]['author']['value'] = $res['copyfrom'] ?  $res['copyfrom'] : 'qqcms' ;
		}

		$array['channel'] =$arraya;
		$Cxml = new Cxml();
		$Cxml->root='rss'; 
		$Cxml->root_attributes=array('version'=>'2.0');
		$xmldata = $Cxml->Cxml($array);
		
		echo $xmldata;
	}

	public function flashxml(){
		$Cxml = new Cxml();
		$Cxml->root='rss'; 
		$Cxml->root_attributes=array('version'=>'2.0');
		$Cxml->NodeName= 'item';
		$xmldata = $Cxml->Cxml($array,'./rss.xml'); //生成xml
	
	}
 
}
?>