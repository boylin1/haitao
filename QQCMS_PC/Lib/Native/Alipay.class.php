<?php
/**
 * 
 * Alipay.php (支付宝支付模块)
 *
 * @package      	QQCMS
 * @author          Ivan QQ:79441928 <admin@qqcms.net>
 * @copyright     	Copyright (c) 2008-2011  (http://www.qqcms.net)
 * @license         http://www.qqcms.net/license.txt
 * @version        	QQCMS网站管理系统 v4.1.5 2012-01-09 qqcms.net $
 * @此注解信息不能修改或删除,请尊重我们的劳动成果,你的修改请注解在此注解下面。
 */
class Alipay extends Think {
	public $config = array()  ;

    public function __construct($config=array()) {
         $this->config = $config;
         /*file_put_contents('config.txt', var_export($config,true));*/
		if ($this->config['alipay_pay_type']==1) $this->config['service'] = 'create_partner_trade_by_buyer'; //担保
		elseif($this->config['alipay_pay_type']==3) $this->config['service'] = 'create_direct_pay_by_user'; //即时
        else $this->config['service'] = 'trade_create_by_buyer';	//标准
        
		$this->config['gateway_url'] = 'https://www.alipay.com/cooperate/gateway.do?';
		$this->config['gateway_method'] = 'POST';
		$this->config['notify_url'] =  return_url('alipay',1);
		$this->config['return_url'] =  return_url('alipay');
    }
	public function setup(){

		$modules['pay_name']    = L('Alipay_pay_name');   
		$modules['pay_code']    = 'Alipay';
		$modules['pay_desc']    = L('Alipay_pay_desc');
		$modules['is_cod']  = '0';
		$modules['is_online']  = '1';
		$modules['author']  = 'QQCMS_PC';
		$modules['website'] = 'http://www.alipay.com';
		$modules['version'] = '1.0.0';
		$modules['config']  = array(
			array('name' => 'alipay_account',           'type' => 'text',   'value' => ''),
			array('name' => 'alipay_key',               'type' => 'text',   'value' => ''),
			array('name' => 'alipay_partner',           'type' => 'text',   'value' => ''),
			array('name' => 'alipay_pay_type',        'type' => 'select', 'value' => '' ,'option' => 
			array('1'=>L('alipay_pay_type_option1'),'2'=>L('alipay_pay_type_option2'),'3'=>L('alipay_pay_type_option3')))
		);

		return $modules;
	}

	public function get_code(){


		$parameter = array(
            'service'           => $this->config['service'],
            'partner'           =>  trim($this->config['alipay_partner']),
            '_input_charset'    =>  'utf-8',
            'notify_url'        =>  trim($this->config['notify_url']),
            'return_url'        =>  trim($this->config['return_url']),
            /* 商品信息 */
            'subject'           => $this->config['subject'],
            'out_trade_no'      => $this->config['order_sn'],
            'price'             => $this->config['order_amount'],
			'body'				=> $this->config['body'],
            'quantity'          => 1,
            'payment_type'      => 1,
            /* 物流参数 */
            'logistics_type'    => 'EXPRESS',
            'logistics_fee'     => 0,
            'logistics_payment' => 'BUYER_PAY_AFTER_RECEIVE',
			//'agent'             => $this->config['agent'], 

            /* 买卖双方信息 */
            'seller_email'      =>  trim($this->config['alipay_account'])
        );
        /*file_put_contents('ff.txt', var_export($parameter,true));*/
        ksort($parameter);
        reset($parameter);
        $param = '';
        $sign  = '';

        foreach ($parameter AS $key => $val)
        {
            $param .= "$key=" .urlencode($val). "&";
            $sign  .= "$key=$val&";
        }

        $param = substr($param, 0, -1);
        $sign  = substr($sign, 0, -1). $this->config['alipay_key'];
        //$sign  = substr($sign, 0, -1). ALIPAY_AUTH;

        $button = '<button type="button" class="btn btn-primary col-xs-3 beijing2"data-target=".bs-example-modal-sm" id="buyNowAddCart" onclick="window.open(\''.$this->config['gateway_url'].$param. '&sign='.MD5($sign).'&sign_type=MD5\')" value="'.L('PAY_NOW').'"><img src="/QQCMS_PC/Tpl/User/Default/Public/images/zhifubao.png" height="27px">支付宝支付</button>';

		return $button;
	}

	public function respond()
    {
		if (!empty($_POST))
        {
            foreach($_POST as $key => $data)
            {
                $_GET[$key] = $data;
            }
        }

        //file_put_contents('cccccc.txt', var_export($_GET,true));
        $seller_email = rawurldecode($_GET['seller_email']);
        //$order_sn = str_replace($_GET['subject'], '', $_GET['out_trade_no']);
        $order_sn = trim($_GET['out_trade_no']);

        /* 检查数字签名是否正确 */
        ksort($_GET);
        reset($_GET);

        $sign = '';
        foreach ($_GET AS $key=>$val)
        {
            if ($key != 'sign' && $key != 'sign_type' && $key != 'code' && $key != 'g' && $key != 'm' && $key != 'a')
            {
                $sign .= "$key=$val&";
            }
        }

        $sign = substr($sign, 0, -1) . $this->config['alipay_key'];
        //$sign = substr($sign, 0, -1) . ALIPAY_AUTH;
        if (md5($sign) != $_GET['sign'])
        {
            return false;
        }

        if ($_GET['trade_status'] == 'WAIT_SELLER_SEND_GOODS' || $_GET['trade_status'] =='WAIT_BUYER_CONFIRM_GOODS' ||  $_GET['trade_status'] =='WAIT_BUYER_PAY')
        {
            /* 改变订单状态 进行中*/
			/*order_pay_status($order_sn,'1');
            return true;*/
            $ro = order_pay_status($order_sn,'1');
            return $ro;
        }
        elseif ($_GET['trade_status'] == 'TRADE_FINISHED')
        {
            /* 改变订单状态 */

            $ro = order_pay_status($order_sn,'2');
            return $ro;
            /*order_pay_status($order_sn,'2');
            return true;*/
        }
        elseif ($_GET['trade_status'] == 'TRADE_SUCCESS')
        {
            /* 改变订单状态 即时交易成功*/		
			//判断是否为商品订单
            $ro = order_pay_status($order_sn,'2');
            return $ro;
        }
        else
        {
            return false;
        }
	}


	
}
?>