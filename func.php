<?php
function get_notice_article($uid,$status,$db_name){
  $sql = "SELECT * FROM `Release_Feed`.`notice` WHERE `flg`= 1 AND `uid`=". $uid ." AND `status`=". $status .";"; 
  $results = mysql_db_query($db_name, $sql);
  $flg = 0;
  while($result = mysql_fetch_assoc($results)){
    $aids[] = $result["aid"];
    $flg = 1;
  }
  if($flg == 1){
    $sql = "SELECT * FROM `article` WHERE `flg` = 1 AND `aid` IN (".implode(',',$aids).") ORDER BY `aid` DESC LIMIT 0,100;";
    if($result = mysql_db_query($db_name, $sql)){
      notice_delete($uid,$status,$db_name);
      return $result;
    }
  }
}
function notice_delete($uid,$status,$db_name){
  $sql = "SELECT * FROM `Release_Feed`.`notice` WHERE `flg` = 1 AND `uid` = ". $uid ." AND `status`=". $status .";";
  $result = mysql_db_query($db_name, $sql);
  if(mysql_fetch_assoc($result)){
    $sql = "UPDATE `Release_Feed`.`notice` SET `flg` = 0 WHERE `uid`= ". $uid .";";
    $result = mysql_db_query($db_name, $sql);
    if($result){
      return true;
    }else{
      return false;
    }
  }else{
    $sql = "INSERT INTO `Release_Feed`.`notice` (`uid`,`aid`,`prcid`,`status`,`flg`) VALUES (".$uid.",".$aid.",".$prcid.",".$status.",1);";
    $result = mysql_db_query($db_name, $sql);
    if($result){
      return true;
    }else{
      return false;
    }
  }
}
function notice_add($uid,$aid,$prcid,$status,$db_name){
  $sql = "SELECT 1 FROM `Release_Feed`.`notice` WHERE `uid`=". $uid ." AND `aid`=". $aid ." AND `prcid`=". $prcid ." AND `status`=". $status .";";
  $result = mysql_db_query($db_name, $sql);
  //既に登録されていたら
  if(mysql_fetch_assoc($result)){
    $sql = "UPDATE `Release_Feed`.`notice` SET `flg` = 1;";
    $result = mysql_db_query($db_name, $sql);
    if($result){
      return true;
    }else{
      return false;
    }
  }else{
    $sql = "INSERT INTO `Release_Feed`.`notice` (`uid`,`aid`,`prcid`,`status`,`flg`) VALUES (".$uid.",".$aid.",".$prcid.",".$status.",1);";
    $result = mysql_db_query($db_name, $sql);
    if($result){
      return true;
    }else{
      return false;
    }
  }
}
function word_search($awords,$db_name){
  $i = 1;
  $sql = "SELECT * FROM `article` WHERE (`flg` = 1) AND";
  foreach ($awords as $word) {
    if($i == 1){
      $sql .= " (`cname` OR `title` LIKE '%".$word."%')";
    }else{
      $sql .=  " AND (`cname` OR `title` LIKE '%".$word."%')";
    }
    $i++;
  }
  $sql .= " ORDER BY `time` DESC LIMIT 0,100;";
  $result = mysql_db_query($db_name, $sql);
  return $result;
}
function search_article_cname($cname,$db_name){
  $sql = "SELECT * FROM `article` WHERE `flg` = 1 AND `cname` LIKE '%".$cname."%' ORDER BY `aid` DESC LIMIT 0,100;";
  $result = mysql_db_query($db_name, $sql);
  return $result;
}
function get_article($db_name,$page){
  $rows = 10;
  $start = ($page - 1) * $rows;
  $sql = "SELECT * FROM `article` WHERE `flg` = 1 ORDER BY `aid` DESC LIMIT ".$start.",".$rows.";";
  $result = mysql_db_query($db_name, $sql);
  return $result;
}
function get_mycomment_aid($uid,$db_name){
  $sql1 = "SELECT `aid` FROM `cid` WHERE `uid` = $uid AND `flg` = 1 ORDER BY `time` DESC;";
  $result1 = mysql_db_query($db_name, $sql1);
  while($aid = mysql_fetch_assoc($result1)){
    $aids[] = $aid["aid"];
  }
  $sql2 = "SELECT * FROM `article` WHERE `flg` = 1 AND `aid` IN (".implode(',',$aids).") ORDER BY `aid` DESC LIMIT 0,100;";
  $result2 = mysql_db_query($db_name, $sql2);
  return $result2;
}
function get_comment($relID,$db_name){
  $sql = "SELECT * FROM `cid` WHERE `aid` = $relID AND `flg` = 1 ORDER BY `time` DESC;";
  $result = mysql_db_query($db_name, $sql);
  return $result;
}
function nikkei_get_nstr($page){
  $nurl = "http://release.nikkei.co.jp/?page=".$page."";
  $nstrsjis = file_get_contents("$nurl");
  $nstr = mb_convert_encoding($nstrsjis, "UTF-8", "SJIS");
  $nstr = file_get_contents("$nurl");
  return $nstr;
}
function nikkei_get_prstr($relID){
  $prurl = "http://release.nikkei.co.jp/detail.cfm?relID=".$relID."";
  $prstrsjis = file_get_contents("$prurl");
  $prstr = mb_convert_encoding($prstrsjis, "UTF-8", "SJIS");
  return $prstr;
}
function nikkei_get_title($prstr){
  $titlestart = strpos ( "$prstr" , '<h1 id="heading" class="heading">') + 33;
  $titleend = strpos ( "$prstr" , '</h1>' , $titlestart) - $titlestart;
  $titlename = substr ("$prstr" , $titlestart, $titleend );
  return $titlename;
}
function nikkei_get_cname($relID,$prstr){
  $cnamestart = strpos ( "$prstr" , '企業名') + 20;
  $cnameend = strpos ( "$prstr" , '|' , $cnamestart) - $cnamestart-6;
  if($cnameend<=0){
    $cnameend = 90;
  }
  $cname = strip_tags(substr ("$prstr" , $cnamestart, $cnameend ));
  return $cname;
}
function nikkei_get_cid($relID,$prstr){
  $cidstart = strpos ( "$prstr" , '株式コード：') + 18;
  $cidend = strpos ( "$prstr" , '</a>' , $cidstart) - $cidstart;
  if($cidstart != 18){
    $cid = substr ("$prstr" , $cidstart, $cidend );
    return $cid;
  }
}
function nikkei_get_article($relID,$prstr){
  $cidstart = strpos ( "$prstr" , '株式コード：') + 18;
  $cidend = strpos ( "$prstr" , '</a>' , $cidstart) - $cidstart;
  if($cidstart != 18){
    $article = substr ("$prstr" , $cidstart, $cidend );
    return $prstr;
  }
}
function nikkei_get_img($relID){
  $img = "";
  $flg = 0;
  for ($i=1; $i < 4; $i++) {
    $imgurl[$i] = "http://release.nikkei.co.jp/attach_file/0".$relID."_0".$i.".jpg";
    $imgstrsjis[$i] = file_get_contents($imgurl[$i]);
    $imgstrutf8[$i] = mb_convert_encoding($imgstrsjis[$i], "UTF-8", "SJIS");
    $response[$i] = strpos($imgstrutf8[$i], "エラー");
    if ($response[$i] === false){
      $img .= "<div class='article_img_box'><a href='".$imgurl[$i]."' target='_blank'><img class='article_img' src='".$imgurl[$i]."'></a></div>";
      $flg++;
    }
  }
  if($flg==0){
    $img = "<div class='article_img_box><img class='article_img' src='img/no_image.jpg'></div>";
  }
  return $img;
}
//Yahoo!から株価
function yahoo_get_stockprice($cid){
  $yurl = "http://stocks.finance.yahoo.co.jp/stocks/detail/?code=".$cid."";
  $ystr = file_get_contents("$yurl");
  $stoksstart = strpos ( "$ystr" , '<td class="stoksPrice">') + 23;
  $stoksend = strpos ( "$ystr" , '</td>',$stoksstart) - $stoksstart;
  if($stoksstart!=23){
    $stoks = substr ("$ystr" , $stoksstart , $stoksend);
    echo "<div><span class='glyphicon glyphicon-usd' aria-hidden='true'></span> 株価 ".$stoks."円</div>";
    $udflg = strpos ( "$ystr" , '前日比');
    $udstart = strpos ( "$ystr" , '">' , $udflg) + 2;
    $udend = strpos ( "$ystr" , '</span>',$udstart) - $udstart;
    $udstoks = substr ("$ystr" , $udstart , $udend);
    echo '<div><span class="glyphicon glyphicon-sort" aria-hidden="true"></span> 前日比 '.$udstoks."</div>";
  }else{
  echo "<div><span class='glyphicon glyphicon-remove-circle' aria-hidden='true'></span> 情報なし</div>";
  }
}
function yahoo_get_stockchart($cid){
  if(isset($cid)&&$cid>0){
    $img = "<a href='http://stocks.finance.yahoo.co.jp/stocks/detail/?code=".$cid."'><img src='http://chart.yahoo.co.jp/?code=".$cid.".T&amp;tm=1d&amp;size=e&amp;vip=off' alt='チャート画像'></a>";
    return $img;
  }else{
    return false;
  }

}
?>