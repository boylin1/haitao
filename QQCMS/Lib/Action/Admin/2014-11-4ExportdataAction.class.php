<?php
/*销售数据导出*/
class ExportdataAction  extends AdminbaseAction{
	protected $star_time=0;
	protected $end_time=0;
	protected $list=array();
	public function index(){
	$this->star_time=strtotime($_POST['star']);
	$this->end_time=strtotime($_POST['end']);
	$this->list=M("user")->field("id,parent_id,groupid,receipt,realname,mobile,beshop_time,status,shop_name,wechat_name,wechat_openid")->where("groupid between 6 and 13")->order(" beshop_time desc")->select();

		$this->list=$this->get_info($this->list);//获取上级、管理年费、此处注意，此函数有获取历史押金记录但已没用，押金改为用user表里的receipt字段记录
		$this->list=$this->get_fee($this->list);//获取自消费、客户消费以及3种返利和下级平台管理分润
		$group=M("role")->getfield("id,name");
		$status=array('0'=>"停止经营",'1'=>"经营中");
		foreach ($this->list as $key => $value) {
			$value['group']=$group[$value['groupid']];
			$value['status']=$status[$value['status']];
			/*组装数组*/
			$data[$key][]=$value['shop_name'];
			$data[$key][]=$value['wechat_name'];
			$data[$key][]=$value['group'];
			$data[$key][]=$value['parent_name'];
			$data[$key][]=date('Y-m-d',intval($value['beshop_time']));
			$data[$key][]=$value['receipt'];
			$data[$key][]=$value['manage'];
			$data[$key][]=$value['self_fee'];
			$data[$key][]=$value['next_fee'];
			$data[$key][]=$value['scale'];
			$data[$key][]=$value['sell_back'];
			$data[$key][]=$value['next_sell_back'];
			$data[$key][]=$value['next_splitt'];
			$data[$key][]=$value['total_back'];
			$data[$key][]=$value['status'];
		}
		$titleList = array(
			'店铺名',
			'微信名',
			'级别',
			'上级单位',
			'成立日期',
			'押金',
			'平台管理年费',
			'自消费金额',
			'客户消费',
			'自消费比例',
			'销售返利',
			'下级返利',
			'下级平台管理费',
			'总返利',
			'经营状态',
		);
		$this->shop_do_export($data,$titleList);
	}
public function menber(){
	$this->star_time=strtotime($_POST['star']);
	$this->end_time=strtotime($_POST['end']);
	$this->list=M("user")->field("id,parent_id,groupid,cash_use,username,realname,mobile,createtime,status,wechat_name,wechat_openid")->where("groupid between 3 and 4")->order(" groupid desc")->select();
		$this->list=$this->get_info($this->list);//获取上级
		$this->list=$this->get_menber_fee($this->list);
		$group=M("role")->getfield("id,name");
		$status=array('0'=>"禁用",'1'=>"激活");
		foreach ($this->list as $key => $value) {
			$value['group']=$group[$value['groupid']];
			$value['status']=$status[$value['status']];
			/*组装数组*/
			$data[$key][]=$value['wechat_name'];
			$data[$key][]=$value['username'];
			$data[$key][]=$value['group'];
			$data[$key][]=$value['parent_name'];
			$data[$key][]=date('Y-m-d',intval($value['createtime']));
			$data[$key][]=$value['pay_cash'];
			$data[$key][]=$value['online_cash'];
			$data[$key][]=$value['cash_use'];
			$data[$key][]=$value['status'];
		}	
		$titleList = array(
			'微信名',
			'用户名',
			'级别',
			'所属微店',
			'成为会员时间',
			'消费金额',
			'已用电子现金额',
			'可用电子现金额',
			'状态',
		);
		$this->shop_do_export($data,$titleList);
	}

protected function shop_do_export($data,$titleList){
	require_once(APP_PATH."phpexcel/PHPExcel.php");
	require_once(APP_PATH."phpexcel/PHPExcel/Writer/Excel5.php");
		$PHPExcel = new PHPExcel();
		$Writer=new PHPExcel_Writer_Excel5($PHPExcel);
		$PHPExcel->setActiveSheetIndex(0);
		$activeSheet = $PHPExcel->getActiveSheet();
		//索引
		$index = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP');
		//表头
	

		$columnWidth = 14; 
		//设置宽度
		foreach ($index as $key => $value) {
			$activeSheet->getColumnDimension($value)->setWidth($columnWidth);
		}
/*		//填入主标题
        $PHPExcel->getActiveSheet()->setCellValue('A1', $_POST['star'].至.$_POST['end'].'有酒派销售数据');  
        //合并单元格
        $PHPExcel->getActiveSheet()->mergeCells('A1:M1');  
        $PHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setName('黑体');
        $PHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
        $PHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);*/
		//输出表头
		$key = 0;
		foreach($titleList as $value) {
			$k = $index[$key].'1';
			$activeSheet->setCellValue($k, $value);
			$activeSheet->getStyle($k)->getFont()->setBold(true);
			$key++;
		}

		$row = 2;
		$_titleList = array_keys($titleList);
		foreach($data as $value) {
			$key = 0;
			foreach($value as $k => $v) {
					$abc = $index[$key].$row;
					$activeSheet->setCellValue($abc, $value[$k].' ');
					$key++;
			}
			$row++;
		}
        //设置居中
        $PHPExcel->getActiveSheet()->getStyle('A1:O'.($row+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            
        //所有垂直居中
        $PHPExcel->getActiveSheet()->getStyle('A1:O'.($row+2))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$_outputFileName = $_POST['star'].至.$_POST['end'].'有酒派销售数据.xls';
		$outputFileName = iconv('utf-8', 'gb2312', $_outputFileName);
		$outputFileName = $outputFileName ? $outputFileName : $_outputFileName;

		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header('Content-Disposition:inline;filename="'.$outputFileName.'"');
		header("Content-Transfer-Encoding: binary");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Pragma: no-cache");

		$Writer->save('php://output');
		exit;
}
/*获取上级、管理年费、押金*/
	public function get_info($list=array()){
	
		foreach ($list as $key => $value) {
			$temp=M('user')->field("realname")->where("id=".$value['parent_id'])->find();
			$list[$key]['parent_name']=empty($temp['realname']) ? "有酒派":$temp['realname'];//获取上级名称
			$list[$key]['manage']=0;
			$wechat_order=M('wechat_order')->field('parent_amount')->where(" userid=".$value['id']." and type=2 and status =1 and pay_time between ".$this->star_time." and ".$this->end_time)->select();
			foreach ($wechat_order as $k => $v) {
				$list[$key]['manage']+=floatval($v['parent_amount']);
			}
		}

		return $list;
	}

/*获取自消费、客户消费、销售返利、下级返利、总返利、下级平台管理分润*/
	public function get_fee($list=array()){
		//var_dump($list);
		foreach ($list as $key => $value) {
			$result=array();//每次获取先清零
			$where=array();
			$where['userid']=$value['id'];
			$where['status']=2;
			$where['pay_time']=array("between",array($this->star_time,$this->end_time));
			$order=M('order')->field('sum(amount) as fee')->where($where)->select();
			$result['self_fee']=$order[0]['fee'];//自消费

			unset($where['userid']);
			$where['shop_id']=$value['id'];
			$order=M('order')->field(' sum(amount) as fee ')->where($where)->select();
			$result['next_fee']=$order[0]['fee'];//客户消费

			$result['sell_back']=$this->sell_back($value['id']);//获取销售返利
			$result['next_sell_back']=$this->next_back($value['id']);//获取下级返利
			if($value['groupid']==6){$result['next_splitt']=$this->next_splitt($value['id']);}//获取下级平台管理分润

			$list[$key]['self_fee']=empty($result['self_fee'])? 0:floatval($result['self_fee']);
			$list[$key]['next_fee']=empty($result['next_fee'])? 0:floatval($result['next_fee']);
			$list[$key]['sell_back']=empty($result['sell_back'])? 0:floatval($result['sell_back']);
			$list[$key]['next_sell_back']=empty($result['next_sell_back'])? 0:floatval($result['next_sell_back']);
			$list[$key]['next_splitt']=empty($result['next_splitt'])? 0:floatval($result['next_splitt']);
			$list[$key]['total_back']=$list[$key]['sell_back']+$list[$key]['next_sell_back'];//总返利
			/*下面获取自消费比例*/
			if($list[$key]['next_fee']==0 && $list[$key]['self_fee']==0){
				$list[$key]['scale']=0;
			}elseif($list[$key]['next_fee']==0 && $list[$key]['self_fee']!=0){
				$list[$key]['scale']="100%";
			}else{
				$result['scale']=round($list[$key]['self_fee']/($list[$key]['self_fee']+$list[$key]['next_fee'])*100,2)."%";
				$list[$key]['scale']=$result['scale']=="0%"? 0:$result['scale'];
			}
		}
	return $list;
	}

/*****************销售返利*******************/
	public function sell_back($user_id){
		$model=M('order');
	$order=$model->field('qq_order_data.product_price,qq_order_data.number,qq_order_data.menber_rebate')->join("`qq_order_data` on `qq_order_data`.order_id=`qq_order`.id")->where(" `qq_order`.shop_id=".$user_id." and `qq_order`.status=2 and `qq_order`.pay_time between ".$this->star_time." and ".$this->end_time)->select();
	//获取订单中商品的总价和返利比率（不取当前商品表中的数据是因为这样计算出来的金额有可能跟消费的金额有误差）
	$sell_back=0;
	foreach ($order as $k => $v) {
		$sell_back+=intval($v['number'])*floatval($v['menber_rebate']);
	}
	return $sell_back;
}

/*****************下级返利*******************/

public function next_back($user_id){
		$sell_back=0;
			$order=M('order')->field(' `qq_order_data`.product_price,`qq_order_data`.number,`qq_order_data`.parent_rebate ')->join("`qq_order_data` on `qq_order_data`.order_id=`qq_order`.id")->where(" `qq_order`.parent_shopid=".$user_id." and `qq_order`.status=2  `qq_order`.pay_time between ".$this->star_time." and ".$this->end_time)->select();
			foreach ($order as $k => $v) {
		$sell_back+=intval($v['number'])*floatval($v['parent_rebate']);
			}
	return $sell_back;
}

/*****************下级平台管理分润*******************/

public function next_splitt($user_id){
		$splitt=0;
		foreach ($user as $key => $value) {
			$wechat_order=M('wechat_order')->field('parent_amount')->where(" parent_shopid=".$value['id']." and type=2 and status =1 and pay_time between ".$this->star_time." and ".$this->end_time)->select();

			foreach ($wechat_order as $k => $v) {
				$splitt+=floatval($v['parent_amount']);
			}

			//$splitt+=$this->next_splitt($value['id']);//下级返利只拿直属下级的客户消费，所以此处暂时屏蔽

		}

	return $splitt;
}
/**********************结束***************/
/*****************下级微店列表*******************/

public function get_next_shop($user_id){
	$user=M('user');
	$count=$user->where("parent_id=".$user_id." and groupid > 6 and groupid< 14")->count();
	$list=$user->field(' `qq_user`.id,`qq_user`.groupid,`qq_user`.createtime,`qq_user`.realname,`qq_user`.shop_name,`qq_user`.mobile,`qq_user`.address,`qq_role`.name  as role_name ')->join(" `qq_role` on `qq_role`.id=`qq_user`.groupid ")->where(" `qq_user`.parent_id=".$user_id." and `qq_user`.groupid > 6 and `qq_user`.groupid< 14")->order(' `qq_user`.id desc ')->select();
	$this->assign('shop_count',$count);
	return $list;
}
/**********************结束***************/

/*****************下级会员列表*******************/
public function get_next_menber($user_id){
	$user=M('user');
	$count=$user->where("parent_id=".$user_id." and groupid < 6")->count();
	$list=$user->field(' `qq_user`.id,`qq_user`.groupid,`qq_user`.createtime,`qq_user`.wechat_name as realname,`qq_user`.mobile,`qq_user`.address,`qq_role`.name as role_name ')->join(" `qq_role` on `qq_role`.id=`qq_user`.groupid ")->where(" `qq_user`.parent_id=".$user_id." and `qq_user`.groupid < 6")->order(' `qq_user`.id desc ')->select();
	$this->assign('menber_count',$count);
	return $list;
}


/**********************结束***************/

/*****************销售订单列表*******************/
	
public function get_order($user_id){
	$order=M('order');
	$list=$order->field("`qq_user`.wechat_name,`qq_user`.realname,`qq_order`.* ")->join(" `qq_user` on `qq_user`.id=`qq_order`.userid ")->where(" `qq_user`.id in (SELECT id from `qq_user` where parent_id={$user_id}) and `qq_order`.status=2 ")->select();
	return $list;
}


/**********************结束***************/
/*****************会员订单列表*******************/
	
public function get_menber_order($user_id){
	$order=M('order');
	$total_price=$order->field(' sum(amount) as total_price')->where(" userid='$user_id' and status=2 ")->find();
	$num=$order->field(" sum(`qq_order_data`.number) as num")->join(" `qq_order_data` on `qq_order_data`.order_id=`qq_order`.id ")->where(' `qq_order`.userid='.$user_id.' and `qq_order`.status=2 ')->find();
	$list=$order->where(" userid={$user_id} and status=2 ")->select();
	$this->assign("menber_num",$num);
	$this->assign("menber_total",$total_price);
	return $list;
}


/**********************结束***************/
/*获取会员消费金额和已用电子现金*/
protected function get_menber_fee($list){
	foreach ($list as $key => $value) {
		$temp=array();
		$list[$key]['pay_cash']=0;
		$list[$key]['online_cash']=0;
		$temp=M("consume")->field("cash,source,level_flat,pay_type")->where("user_id={$value['id']} and source in(1,2,3,4,6) and level_flat=0 and pay_type!=3 and create_time  between ".$this->star_time." and ".$this->end_time)->select();
		foreach ($temp as $key => $value) {
			$list[$key]['pay_cash']+=floatval($temp['cash']);
			if($value['pay_type']==2&&$value['source']==1){
				$list[$key]['online_cash']+=floatval($temp['cash']);
			}
		}
	}
	return $list;
}
/********************************/
}
?>