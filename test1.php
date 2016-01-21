
<?php
/**
 * 爬取中国联通网站的通话记录
 *
 *
 */
header("Content-Type:text/html;charset=UTF-8");
/**
 * Blog   www.wujunze.com
 * Email  itwujunze@163.com
 * Date:
 */

//var_dump($_POST);
$num = $_POST['num'];
$pwd = $_POST['pwd'];
$start = $_POST['start'];
$end = $_POST['end'];
//exit;
//获取PHP毫秒数
include "./Snoopy.class.php";
function getMillisecond()
{
    list($s1, $s2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
}

//把object装换成array
function std_class_object_to_array($stdclassobject)
{
    $_array = is_object($stdclassobject) ? get_object_vars($stdclassobject) : $stdclassobject;
    $array = array();
    foreach ($_array as $key => $value) {
        $value = (is_array($value) || is_object($value)) ? std_class_object_to_array($value) : $value;
        $array[$key] = $value;
    }
    return $array;
}
// jul cookie
$userAgent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.155 Safari/537.36";
$userName = $num;//用户名
$userPwd = $pwd;//密码


$loginurl = "https://uac.10010.com/portal/Service/MallLogin?userName=".$userName."&password="
    . $userPwd . "&pwdType=01&productType=01&redirectType=03"; // "&uvc=" . $uvc;

$snoopy = new Snoopy();
$content = $snoopy->fetch($loginurl);

// var_dump($content);

$array = std_class_object_to_array($content);
$JULCookie = explode(":",$array['headers'][6])[1];
$JULCookie = explode(";", $JULCookie)[0];
//echo $JULCookie."---------";
// 2、e3cookie
$snoopy = new Snoopy();
$snoopy->_httpmethod = 'POST';
$snoopy->rawheaders = array(
    'User-Agent'       => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:40.0) Gecko/20100101 Firefox/40.0',
    'X-Requested-With' => 'XMLHttpRequest',
    'Referer'          => 'http://iservice.10010.com/e3/query/call_dan.html?menuId=000100030001',
    'Cookie'           => 'mallcity=11|110; WT_FPC=id=2b5e76bb74bc89f0650'.getMillisecond().':lv=' . time() . ':ss=1442223922628; '.$JULCookie .'; ',
    'Content-Length'   => 0,
);

$content = $snoopy->fetch('http://iservice.10010.com/e3/static/check/checklogin/?_=' . getMillisecond());

//var_dump($content);
$array2 = std_class_object_to_array($content);
$e3Cookie = '';
//$e3Cookie .= explode(";",explode(":", $array2['headers'][5])[1])[0]."; ";
//$e3Cookie .= explode(";",explode(":",$array2['headers'][7])[1])[0]."; ";
$e3Cookie .= explode(";",explode(":",$array2['headers'][6])[1])[0]."; ";

//echo $e3Cookie."-----------";




//请求联通的通话记录接口
$snoopy = new Snoopy();
$snoopy->rawheaders = array(
    'User-Agent'        => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:40.0) Gecko/20100101 Firefox/40.0',
    'X-Requested-With'  => 'XMLHttpRequest',
    'Referer'           => 'http://iservice.10010.com/e3/query/call_dan.html?menuId=000100030001',
    'Cookie'            => $JULCookie."; ".$e3Cookie."; ",
);

$post = array(
    'beginDate'  => $start,//起始时间
    'endDate'   => $end,//终止时间
    //'pageNo'    => '1',//分页
    'pageSize'  => 1000//每页数量
);
$content = $snoopy->submit('http://iservice.10010.com/e3/static/query/callDetail?_=' . getMillisecond() . '&menuid=000100030001', $post);

$cu = json_decode($content->results, true);
// echo "<pre>";
// var_dump($cu);
// echo "</pre>";
echo "<a href='./index.html'>继续查询</a></br>";
echo "客户姓名 :".$cu['userInfo']['custName']."</br>";
echo "客户手机号 :".$cu['userInfo']['usernumber']."</br>";
echo "查询周期 :".$cu['queryDateScope']."</br>";
echo "<table border=0 aligen=center>";
echo "<tr><td>起始时间</td><td>通话时长</td><td>对方号码</td><td>呼叫类型</td><td>通话类型</td></tr>";


$jilu = $cu['pageMap']['result'];
foreach ($jilu as $key => $value) {
    echo "<tr><td>".$value['calldate']."---".$value['calltime']."</td><td>".$value['calllonghour']."</td><td>".$value['othernum']."</td><td>".$value['calltypeName']."</td><td>".$value['landtype']."</td></tr>";
}
echo "</table>";




