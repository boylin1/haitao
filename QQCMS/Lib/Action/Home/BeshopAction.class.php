<?php
/**
 * 
 * 申请成为经营者表单
 *
 */
class BeshopAction extends BaseAction {
	public function index(){
		if($_POST['sub']=='submit'){
			if(!$_POST['shop_name']||!$_POST['email']||!$_POST['real_name']||!$_POST['id_number']||!$_POST['mobile']||!$_POST['address']||!$_POST['account_number']||!$_POST['account_name']||!$_POST['bank_name']){
				$res['status']=0;
				$res['msg']="请填写完整表单信息再提交";
				echo json_encode($res);exit();
			}
			
			if (!$this->_userid)
			{
				$res['status']=0;
				$res['msg']="请先登录";
				echo json_encode($res);exit();
			}
			
			$shop=M("beshop")->field('id')->where('userid='.intval($this->_userid))->find();
			if($shop){
				$res['status']=0;
				$res['msg']="您已完成过申请，我们将于5个工作日内，把申请结果发至您的邮箱，请耐心等候！";
				echo json_encode($res);exit();
			}
			/*end*/

			$_POST = $this->stripslashes_array($_POST);//数据过滤
			foreach ($_POST as $key => $value) {
				$_POST[$key]=$this->lib_replace_end_tag($value);
			}
			// $img_info=$this->upload();
			// $_POST['id_img']=$img_info[0]['savepath'].$img_info[0]['savename'];//身份证复印件地址
			$_POST['userid']=intval($this->_userid);
			$_POST['createtime']=time();
			$res=M('beshop')->add($_POST);
			
			if($res){
				$data['status']=1;
				$data['msg']="您已完成申请成为上优舶合作方，我们将于5个工作日内，把申请结果发至您的邮箱，请耐心等候！";
				echo json_encode($data);exit();
			}
			else{
				$data['status']=0;
				$data['msg']="提交失败";
				echo json_encode($data);exit();
			}
		}
			$this->display();
	}	
	public function terms(){
		$this->display();
	}
function shop_error($msg){
		$this->assign("message",$msg);
		$this->display("Beshop:shop_error");
}
function stripslashes_array(&$array) {
	 while(list($key,$var) = each($array)) {
 		 if ($key != 'argc' && $key != 'argv' && (strtoupper($key) != $key || ''.intval($key) == "$key")) {
  		 if (is_string($var)) {
 	   $array[$key] = stripslashes($var);
  		 }
  	 if (is_array($var))  {
  	  $array[$key] = stripslashes_array($var);
  	 }
 	 }
 }
 return $array; 
}
function lib_replace_end_tag($str)
{
 if (empty($str)) return false;
 $str = htmlspecialchars($str);
 $str = str_replace( '/', "", $str);
 $str = str_replace("\\", "", $str);
 $str = str_replace(">", "", $str);
 $str = str_replace("<", "", $str);
 $str = str_replace("<SCRIPT>", "", $str);
 $str = str_replace("</SCRIPT>", "", $str);
 $str = str_replace("<script>", "", $str);
 $str = str_replace("</script>", "", $str);
 $str=str_replace("select","select",$str);
 $str=str_replace("join","join",$str);
 $str=str_replace("union","union",$str);
 $str=str_replace("where","where",$str);
 $str=str_replace("insert","insert",$str);
 $str=str_replace("delete","delete",$str);
 $str=str_replace("update","update",$str);
 $str=str_replace("like","like",$str);
 $str=str_replace("drop","drop",$str);
 $str=str_replace("create","create",$str);
 $str=str_replace("modify","modify",$str);
 $str=str_replace("rename","rename",$str);
 $str=str_replace("alter","alter",$str);
 $str=str_replace("cas","cast",$str);
 $str=str_replace("&","&",$str);
 $str=str_replace(">",">",$str);
 $str=str_replace("<","<",$str);
 $str=str_replace(" ",chr(32),$str);
 $str=str_replace(" ",chr(9),$str);
 $str=str_replace("    ",chr(9),$str);
 $str=str_replace("&",chr(34),$str);
 $str=str_replace("'",chr(39),$str);
 $str=str_replace("<br />",chr(13),$str);
 $str=str_replace("''","'",$str);
 $str=str_replace("css","'",$str);
 $str=str_replace("CSS","'",$str); 
 return $str;  
}
	public function upload(){
			import ( '@.ORG.UploadFile' );
			$upload=new UploadFile();
   			 $upload->maxSize  = 3145728 ;// 设置附件上传大小
    		$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
   			 $upload->savePath =  'Public/Beshop_uploads/';// 设置附件上传目录
   			 $upload->saveRule = time().mt_rand(0,99); 
  		  if(!$upload->upload()) {// 上传错误提示错误信息
      	  $this->error($upload->getErrorMsg());
        exit();
   		 }else{// 上传成功
   		 	//取得成功上传的文件信息
			return $upload->getUploadFileInfo();
    	}
	}
}
?>