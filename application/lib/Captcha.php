<?php
class Captcha
{
	public static function generate_simple()
	{
		$captcha = imagecreatefrompng(CAPTCHA_BASE_IMG_PATH);
		$opd1 = mt_rand(3, 9);
		$opd2 = mt_rand(1, $opd1);
		$opt = mt_rand(1, 3);

		switch ($opt)
		{
			case 1:
				$result = $opd1 + $opd2;
				$str_opt = '+';
				break;
			case 2:
				$result = $opd1 - $opd2;
				$str_opt = '-';
				break;
			case 3:
				$result = $opd1 * $opd2;
				$str_opt = 'x';
				break;
		}
		$font = 5;
		$c1 = rand(0, 155);
		$c2 = rand(0, 155);
		$c3 = rand(0, 155);
		$colour = imagecolorallocate($captcha, $c1, $c2, $c3);
		imagestring($captcha, $font, 25, 7, $opd1, $colour);
		imagestring($captcha, $font, 55, 7, $str_opt, $colour);
		imagestring($captcha, $font, 85, 7, $opd2, $colour);
		imagestring($captcha, $font, 115, 7, '=', $colour);
		imagestring($captcha, $font, 145, 7, '?', $colour);

		$_SESSION['captcha'] = md5($result);
		imagepng($captcha);
	}

/*
	public static function generate()
	{
		// DECLARE SESSION
		@session_start();
		// LENGTH OF CAPTCHA STRING
		$length=5;
		// FONT SIZE 1 (SMALLEST) - 5 (LARGEST)
		$font = 5;
		// POSSIBLE CHARACTERS IN CAPTCHA STRING
		//$possible = "23456789bcdfghjkmnpqrstvwxyz";
		$possible = "23456789ABCDEFGHJKLMNPQRSTWZV";
		// PATH TO PNG BACKGROUND IMAGE
		$captcha = imagecreatefrompng(CAPTCHA_BASE_IMG_PATH);
		$i = 0;
		$g = 20;
		$hash = "";

		// CYCLE THROUGH LETTERS TO MAKE STRING
		while ($i < $length) {
			// GET RANDOM COLOURS, 0-255
			// IN THIS EXAMPLE I HAVE SELECTED DARK COLOURS ONLY SO THE
			// S ARE READABLE ON THE LIGHT BACKGROUND
			$c1 = rand(0, 155);
			$c2 = rand(0, 155);
			$c3 = rand(0, 155);
			$colour = imagecolorallocate($captcha, $c1, $c2, $c3);
			// CHOOSE RANDOM CHARACTER
			$string = substr($possible, mt_rand(0, strlen($possible)-1), 1);
			// BUILD STRING TO SEND TO PROCESSING
			$hash  .= $string;
			// WRITE STRING TO IMAGE
			imagestring($captcha, $font, $g, 10, $string, $colour);
			$i++;
			$g = ($g + 20);
			//  CLEAR LETTERS TO STOP DUPLICATES
			unset($string);
		}
		// SET ENCRYPTED CAPTCHA STRING TO A SESSION STRING
		$_SESSION['captcha'] = md5($hash);
		// OUTPUT CAPTCHA IMAGE
		imagepng($captcha);
	}
*/
	public static function validate($code)
	{
		if ( ! $code OR ! isset($_SESSION['captcha'])){
			return FALSE;
		}
		$captcha = $_SESSION['captcha'];
		unset($_SESSION['captcha']);
		return md5($code) == $captcha;
	}
}
