<?php

class Password
{
	public function validate($password, $re_password)
	{
		$msg = array();

		//check the length of the password
		if (iconv_strlen($password) < MIN_PASSWORD_LENGTH || iconv_strlen($password) > MAX_PASSWORD_LENGTH)
			$msg[] = "Mật khẩu phải nằm trong khoảng từ " . MIN_PASSWORD_LENGTH . " đến " . MAX_PASSWORD_LENGTH . " ký tự. ";

		//check if two password are matched
		if ($password != $re_password)
			$msg[] = "Mật khẩu và xác nhận mật khẩu không khớp. ";

		return $msg;
	}
}