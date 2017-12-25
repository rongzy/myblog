<!DOCTYPE html>
<html>
	<head>

		<link rel="stylesheet" href="../css/info.css" />
		<script type="text/javascript" src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js" ></script>
		<script type="text/javascript" src="../Js/index.js" ></script>
		  <script type="text/javascript" src="../Js/qrcode.js"></script>
		  <script type="text/javascript">
		  	function goback(){
		  		window.location.href="http://www.8887060.com";
		  	}
		  </script>
		<title></title>
	</head>

	<?php
	// 判断是否是扫码付款
	include("../conf/config.php");
	if(empty($_POST['name']) || empty($_POST['price'])){
		 echo "<script > alert('请填写用户名和充值金额');window.history.back();</script>";exit();
	}
	if(empty($_POST['pay_type'])){
		 echo "<script > alert('请选择支付方式');window.history.back();</script>";exit();
	}
	$_POST['price'] = (double) $_POST['price'];
	if($_POST['pay_way'] == "1"){
	$url = "http://47.92.69.227/gateway/payment";
	$sql = "select `merchant`,`url`,`key` from ms_cate where payname ='jxpay' limit 1";
	$res = mysqli_query($db,$sql);
	$data = mysqli_fetch_array($res,MYSQLI_ASSOC);
	$list['version'] ='1.0'; //商户号
	$list['agentId'] =$data['merchant']; //商户号
	$list['agentOrderId'] = 'aajxpayaa'.date("YmdHis").rand(000,999);
	switch ($_POST['pay_type']) {
		case 'wx':
			$list['payType'] = "10"; //支付类型21-微信，30-支付宝,31-QQ钱包
    		$cate_id = 84;
			break;
		case 'ali':
			$list['payType'] = "30"; //支付类型21-微信，30-支付宝,31-QQ钱包
    		$cate_id = 83;
			break;
		case 'QQ':
			$list['payType'] = "60"; //支付类型21-微信，30-支付宝,31-QQ钱包
	 		$cate_id = 85;
			break;
	}

	$list['payAmt'] = $_POST['price'];//充值金额
	$list['orderTime'] = date("YmdHis");//充值金额
	$list['payIp'] = $_SERVER['REMOTE_ADDR'];
	$list['notifyUrl'] = $data['url']; //异步通知地址
	$md5Key = $data['key'];
/* 构建签名原文 */
	function Sign($param) {
		$string = '';
		foreach((array)$param as $k => $value) {
			$string .= $value . '|';
		}
		return $string;
	}

		// 模拟发送
	function send_post($url, $data)
	{
	    $postdata = http_build_query($data);
	    $options = array(
	        'http' => array(
	            'method' => 'POST',
	            'header' => 'Content-type:application/x-www-form-urlencoded',
	            'content' => $postdata,
	            'timeout' => 15 * 60
	        ) // 超时时间（单位:s）

	    );
	    $context = stream_context_create($options);
	    $result = file_get_contents($url, false, $context);
	    return $result;
	}


	$param = Sign($list);
	//var_dump($param);die;
	$list['sign'] = md5($param.$md5Key);

// var_dump($param);die;
	$response = send_post($url,$list);

	var_dump($response);die;
	preg_match('{<code>(.*?)</code>}', $response, $match);
	$respCode = $match[1];//响应码
	preg_match('{<qrCode>(.*?)</qrCode>}', $response, $match);
	$respqrCode= $match[1];//二维码
	$qrcode = base64_decode($respqrCode);
	if($respCode == "00"){
	        $orders_id =  $list['tradeNo'];
	        $orders_user = $_POST['name'];
	        $orders_money = $_POST['price'];
	        $ssl_sign = json_encode($list);
	        $client_ip = $_SERVER['REMOTE_ADDR'];
	        $create_time = date("Y-m-d H:i:s");
			$sql = "insert into ms_online(orders_id,cate_id,orders_user,orders_money,ssl_sign,client_ip,create_time) values('{$orders_id}','{$cate_id}','{$orders_user}','{$orders_money}','{$ssl_sign}','{$client_ip}','{$create_time}')";
	        mysqli_query($db,$sql);
		?>
	<body>
		<div id="power">
				<header class="hed">
					<div class="hed-1"><img src="../images/LOGO.png"></div>
				</header>
				<section id="container">
					<div class="ht">
						<img src="../images/<?php echo $_POST['pay_type']?>.png"/>
					</div>
					<div class="aom">
						<p>
							本次需充值<span class="je"><?php echo $_POST['price'];?></span>元
						</p>
						<p>
							温馨提示：请务必按照以上提交金额进行支付，否则无法即时到账
						</p>
						<div class="ewm" id="showqrcode">

						</div>
					</div>
					<div class="ifa">
						<p>交易单号:<span class="right"><?php echo $list['tradeNo'];?></span></p>
						<p>创建时间:<span class="right"><?php echo  $create_time;?></span></p>
					</div>
					<input type="button" value="" class="bnt" onclick="goback()" />
					<footer class="fot">
					<p>Copyright 2009-2017&copy;幸运彩票 保留所有权利</p>
					<p>幸运彩票 郑重提示：彩票有风险，投注需谨慎，不向未满18周岁的青少年出售彩票</p>
				</footer>
				</section>

			</div>
			    <script type="text/javascript">
			        (function(){
			                new QRCode(document.getElementById('showqrcode'), '<?php echo $qrcode;?>');
			            }
			            )();
			    </script>
			    <?php
				}else{
				    echo "<script > alert('该支付方式正在维护中，请更换支付方式重新支付');window.history.back();</script>";
				}
				?>
	<?php
	}elseif($_POST['pay_way'] == "2"){

		$orders_id = $_POST['name'].'online'.date("YmdHis");
		$price = $_POST['price'];
		$time = date("Y-m-d H:i:s");
		$pay_type = $_POST['pay_type'];

		switch ($_POST['pay_type']) {
			case 'wx':
				$id =13;
				$images ="wx.png";
				break;
			case 'ali':
				$id =12;
				$images ="ali.png";
				break;
			case 'QQ':
				$id =11;
				$images ="QQ.png";
				break;
			case 'jd':
				$id =14;
				$images ="jd.png";
				break;
			case 'bd':
				$id = 15;
				$images ="bd.png";
				break;
		}
		$sql = "select cate_icon,qr_code,opstate from ms_cate where id = {$id} and opstate = 1";
		$res = mysqli_query($db,$sql);
		$data = $res->fetch_array(MYSQLI_ASSOC);
		$qr_code = $data['qr_code'];
		$status = $data['opstate'];
		if(!$status){
			  echo "<script > alert('该支付方式正在维护中，请更换支付方式重新支付');window.history.back();</script>";exit();
		}
 	$orders_id =  $orders_id;
        $orders_user = $_POST['name'];
        $orders_money = $_POST['price'];
        $client_ip = $_SERVER['REMOTE_ADDR'];
        $create_time = date("Y-m-d H:i:s");
        $sql = "insert into ms_company(orders_id,cate_id,orders_user,orders_money,client_ip,create_time) values('{$orders_id}','{$id}','{$orders_user}','{$orders_money}','{$client_ip}','{$create_time}')";
        mysqli_query($db,$sql);
		?>
			<body>
	<div id="power">
			<header class="hed clearfix">
				<div class="hed-1 left"><img src="../images/LOGO.png"></div>
			</header>
			<section id="container">
				<div class="ht">
					<img src="../images/<?php echo $images;?>"/>
				</div>
				<div class="aom">
					<p>
						本次需充值<span class="je"><?php echo $price;?></span>元
					</p>
					<p>
						温馨提示：请务必按照以上提交金额进行支付，否则无法即时到账
					</p>
					<div class="ewm">
						<img src="http://image.8887060.com/<?php echo $qr_code;?>">
					</div>
				</div>
				<div class="ifa">
					<p>交易单号:<span class="right"><?php echo $orders_id;?></span></p>
					<p>创建时间:<span class="right"><?php echo $time;?></span></p>
				</div>
				<input type="button" value="" class="bnt" onclick="goback()"/ >
				<footer class="fot">
				<p>Copyright 2009-2017&copy;幸运彩票 保留所有权利</p>
				<p>幸运彩票 郑重提示：彩票有风险，投注需谨慎，不向未满18周岁的青少年出售彩票2</p>
			</footer>
			</section>

		</div>
	<?php
	}else{
		$url = "http://gate.lfbpay.com/cooperate/gateway.cgi";
	$sql = "select `merchant`,`url`,`key` from ms_cate where payname ='starpay' limit 1";
	$res = mysqli_query($db,$sql);
	$data = mysqli_fetch_array($res,MYSQLI_ASSOC);
	$list['merId'] =$data['merchant']; //商户号
	$list['amount'] = $_POST['price'];//充值金额
	$list['notifyUrl'] = $data['url']; //异步通知地址
	$list['service'] = "TRADE.B2C";
	$list['version'] = "1.0.0.0";
	$md5Key = $data['key'];
	$list['typeId'] = " "; //支付类型21-微信，30-支付宝,31-QQ钱包
	$cate_id = 78;
	$list['tradeNo'] = 'aastarpayaa'.date("YmdHis").rand(000,999); //订单号
	$list['tradeDate'] =date("Ymd");
	$list['summary'] = "xycp";
	$list['extra'] = "";
	$list['expireTime'] = "600";

	$list['clientIp'] = $_SERVER['REMOTE_ADDR'];
		function sign_src($data)
	{
	   $result = sprintf(
					"service=%s&version=%s&merId=%s&typeId=%s&tradeNo=%s&tradeDate=%s&amount=%s&notifyUrl=%s&extra=%s&summary=%s&expireTime=%s&clientIp=%s",
					$data['service'],
					$data['version'],
					$data['merId'],
					$data['typeId'],
					$data['tradeNo'],
					$data['tradeDate'],
					$data['amount'],
					$data['notifyUrl'],
					$data['extra'],
					$data['summary'],
					$data['expireTime'],
					$data['clientIp']


			);

			return $result;

	}

	$param = sign_src($list);
	$sign = md5($param.$md5Key);
	?>
<body onLoad="document.onlinepay.submit();">
		<form action="<?php echo ($url) ?>" name="onlinepay"	method="post">
			<input type="hidden" class="form-control" 	name="merId" value="<?php echo ($list['merId'])?>" />
			<input type="hidden" class="form-control" 	name="amount" value="<?php echo ($list['amount'])?>" />
			<input type="hidden" class="form-control" 	name="notifyUrl" value="<?php echo ($list['notifyUrl'])?>" />
			<input type="hidden" class="form-control" 	name="service" value="<?php echo ($list['service'])?>" />
			<input type="hidden" class="form-control" 	name="version" value="<?php echo ($list['version'])?>" />
			<input type="hidden" class="form-control" 	name="typeId" value="<?php echo ($list['typeId'])?>" />
			<input type="hidden" class="form-control" 	name="tradeNo" value="<?php echo ($list['tradeNo'])?>" />
			<input type="hidden" class="form-control" 	name="tradeDate" value="<?php echo ($list['tradeDate'])?>" />
			<input type="hidden" class="form-control" 	name="extra" value="<?php echo ($list['extra'])?>" />
			<input type="hidden" class="form-control" 	name="expireTime" value="<?php echo ($list['expireTime'])?>" />
			<input type="hidden" class="form-control" 	name="clientIp" value="<?php echo ($list['clientIp'])?>" />
			<input type="hidden" class="form-control" 	name="sign" value="<?php echo ($sign)?>" />

		</form>

	<?php
	}

	?>
	</body>
</html>
