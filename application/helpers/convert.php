<?php
function bb2html($text)
{
  $bbcode = array("<", ">",
                "[LIST]", "[*]", "[/LIST]", 
                "[IMG]", "[/IMG]", 
                "[B]", "[/B]", 
                "[U]", "[/U]", 
                "[I]", "[/I]",
                '[COLOR="', '[/COLOR]',
                '[SIZE="', '[/SIZE]',
                '[URL="', "[/URL]",
                '[MAIL="', "[/MAIL]",
                "[CODE]", "[/CODE]",
                "[QUOTE]", "[/QUOTE]",
                '"]');
  $htmlcode = array("&lt;", "&gt;",
                "<ul>", "<li>", "</ul>", 
                '<img src="', '">', 
                "<b>", "</b>", 
                "<u>", "</u>", 
                "<i>", "</i>",
                '<span style="color:', "</span>",
                '<span style="font-size:', "</span>",
                '<a href="', "</a>",
                '<a href="mailto:', "</a>",
                "<code>", "</code>",
                "<blockquote class='vtquote'>", "</blockquote>",
                '">');
  $newtext = str_replace($bbcode, $htmlcode, $text);
  $newtext = nl2br($newtext);//second pass
  return $newtext;
}


function word_limiter( $text, $limit = 30, $chars = '0123456789' ) {
    if( strlen( $text ) > $limit ) {
		$arr = explode(" ",$text);
		$i = 0;
		$words = "";
		
		while ($i < $limit) {
			$words .= @$arr[$i]. " ";
			$i++;
		}
        $text = $words . '&hellip;';
    }
    return $text;
}

// display x time ago, $rcs is precision depth
function time_ago($tm, $rcs = 0) {
	if (empty($tm)) $tm = strtotime("-10 hours");

  $cur_tm = time(); 
  $dif = $cur_tm - $tm;
  $pds = array('seconds', 'minutes', 'hour', 'day', 'week', 'month', 'year', 'decade');
  $lngh = array(1,60,3600,86400,604800,2630880,31570560,315705600);

  for ($v = count($lngh) - 1; ($v >= 0) && (($no = $dif / $lngh[$v]) <= 1); $v--);
    if ($v < 0)
      $v = 0;
  $_tm = $cur_tm - ($dif % $lngh[$v]);

  $no = ($rcs ? floor($no) : round($no)); // if last denomination, round

  if ($no != 1)
    $pds[$v] .= '';
  $x = $no . ' ' . $pds[$v];

  if (($rcs > 0) && ($v >= 1))
    $x .= ' ' . $this->time_ago($_tm, $rcs - 1);
	
	if ($v > 2) {
		return date('d/m/Y h:i A',$tm);
	}
	
	if ($dif == 0) return "recently.";
	
  return $x . ' ago.';
  
}

function show_title($point) {
	if ($point >= VIP_KIMCUONG) echo "VIP Kim Cương";
	else if ($point >= VIP_VANG) echo "VIP Vàng";
	else if ($point >= VIP_BAC) echo "VIP Bạc";
	else if ($point >= VIP_DONG) echo "VIP Đồng";
	else echo "Nhập môn";
}

function rewrite_url($str) {
	$maTViet=array("à","á","ạ","ả","ã","â","ầ","ấ","ậ","ẩ","ẫ","ă",
		"ằ","ắ","ặ","ẳ","ẵ","è","é","ẹ","ẻ","ẽ","ê","ề"
		,"ế","ệ","ể","ễ",
		"ì","í","ị","ỉ","ĩ",
		"ò","ó","ọ","ỏ","õ","ô","ồ","ố","ộ","ổ","ỗ","ơ"
		,"ờ","ớ","ợ","ở","ỡ",
		"ù","ú","ụ","ủ","ũ","ư","ừ","ứ","ự","ử","ữ",
		"ỳ","ý","ỵ","ỷ","ỹ",
		"đ",
		"À","Á","Ạ","Ả","Ã","Â","Ầ","Ấ","Ậ","Ẩ","Ẫ","Ă"
		,"Ằ","Ắ","Ặ","Ẳ","Ẵ",
		"È","É","Ẹ","Ẻ","Ẽ","Ê","Ề","Ế","Ệ","Ể","Ễ",
		"Ì","Í","Ị","Ỉ","Ĩ",
		"Ò","Ó","Ọ","Ỏ","Õ","Ô","Ồ","Ố","Ộ","Ổ","Ỗ","Ơ"
		,"Ờ","Ớ","Ợ","Ở","Ỡ",
		"Ù","Ú","Ụ","Ủ","Ũ","Ư","Ừ","Ứ","Ự","Ử","Ữ",
		"Ỳ","Ý","Ỵ","Ỷ","Ỹ",
		"Đ"," ",".",",","?",":",";","&",'/','%','(','*',')','!','[',']',"'");
		
	$maKoDau=array("a","a","a","a","a","a","a","a","a","a","a"
		,"a","a","a","a","a","a",
		"e","e","e","e","e","e","e","e","e","e","e",
		"i","i","i","i","i",
		"o","o","o","o","o","o","o","o","o","o","o","o"
		,"o","o","o","o","o",
		"u","u","u","u","u","u","u","u","u","u","u",
		"y","y","y","y","y",
		"d",
		"A","A","A","A","A","A","A","A","A","A","A","A"
		,"A","A","A","A","A",
		"E","E","E","E","E","E","E","E","E","E","E",
		"I","I","I","I","I",
		"O","O","O","O","O","O","O","O","O","O","O","O"
		,"O","O","O","O","O",
		"U","U","U","U","U","U","U","U","U","U","U",
		"Y","Y","Y","Y","Y",
		"D","-","-","-",'-',"-","-",'-','-','','','','','','','','-');
		
		return str_replace($maTViet,$maKoDau,$str);
}
?>