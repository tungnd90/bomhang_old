<?php

class Email
{
	public function validate($email)
	{
		$msg = array();

		//check if this is a valid email address
		if ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email))
			$msg[] = 'Bạn nhập vào một địa chỉ email không hợp lệ. ';

		//check if this email address was used

		$um = ClassFactory::getInstance()->get_model('UsersModel');
		$existed_user = $um->getUserByEmail($email);
		if ($existed_user)
			$msg[] = 'Địa chỉ email này đã được sử dụng để đăng ký tài khoản. ';

		return $msg;

	}
}