<?php
class Controller_Fibo extends Controller
{
	public function index()
	{
		$iplist = array('203.171.30.222','118.69.199.9');
		if(in_array($_SERVER['REMOTE_ADDR'],$iplist)||1){
			//die(var_dump($_GET));
			$message = @$_GET['message']; // Nội dung tin
			$phone   = @$_GET['phone']; // số điện thoại của KH
			$service = @$_GET['service']; // mã dịch vụ
			$port    = @$_GET['port'];  // đầu số
			$main    = @$_GET['main'];  //keyword
			$sub     = @$_GET['sub'];  // prefix
			// Hết lấy nội dung tin nhắn

			$md5id = md5(uniqid(rand(), true));
			$message= strtolower($message);
			$m=explode(" ",$message);
			$r = 'Xin chao, noi dung tin nhan khong hop le "'.$message;
			$rand = rand(1000,9999);
			$phone = "0".substr($phone,2,strlen($phone));
			if($m[1]=='ndcc'){
				if($m[0]=='reg'){
					$user_exist = Model_User::fetch(array('phone' => $phone),1);
					if ($user_exist){
						$user        = $user_exist[0];
					} else {
						$user        = new Model_User();
						$user->phone = $phone;
					}
					$user->otp = $rand;
					$user->password = NULL;
					$user->save();
					$r = sprintf($this->appsite['sms_confirm'],$rand);
				}
			}

			echo '
			<ClientResponse>
				<Message>
					<PhoneNumber>'.$phone.'</PhoneNumber>
					<Message>'.$r.'</Message>
					<SMSID>'.$md5id.'</SMSID>
					<ServiceNo>'.$service.'</ServiceNo>
				</Message>
			</ClientResponse>';
		}
		exit;
	}
}