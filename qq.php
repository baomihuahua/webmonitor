<?php
////////////////////////////////////////////////////////////////////
//                          _ooOoo_                               //
//                         o8888888o                              //
//                         88" . "88                              //
//                         (| ^_^ |)                              //
//                         O\  =  /O                              //
//                      ____/`---'\____                           //
//                    .'  \\|     |//  `.                         //
//                   /  \\|||  :  |||//  \                        //
//                  /  _||||| -:- |||||-  \                       //
//                  |   | \\\  -  /// |   |                       //
//                  | \_|  ''\---/''  |   |                       //
//                  \  .-\__  `-`  ___/-. /                       //
//                ___`. .'  /--.--\  `. . ___                     //
//              ."" '<  `.___\_<|>_/___.'  >'"".                  //
//            | | :  `- \`.;`\ _ /`;.`/ - ` : | |                 //
//            \  \ `-.   \_ __\ /__ _/   .-` /  /                 //
//      ========`-.____`-.___\_____/___.-`____.-'========         //
//                           `=---='                              //
//      ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^        //
//               佛祖保佑       永不宕机     永无BUG              //
//                         www.boxmoe.com                         //
////////////////////////////////////////////////////////////////////
$config = json_decode(file_get_contents(__DIR__ . "/config.json"), true);
//日志输出，看需求不需要就注释
$logPath = __DIR__ . "/web_status.log";
$curl = curl_init();

//错误输出
function errLog($msg)
{
    global $logPath;
    $message = "[错误]" . date("[y-m-d H:i:s]") . " $msg\n";
    error_log($message, 3, $logPath);
	//网页输出，按需不需要就注释
	echo "<p id='err'>".$message."</p>" ;
}
//正常输出
function infoLog($msg)
{
    global $logPath;
    $message = "[正常]" . date("[y-m-d H:i:s]") . " $msg\n";
    error_log($message, 3, $logPath);
	//网页输出，按需不需要就注释
	echo "<p id='info'>".$message."</p>\n";
}

//开始QQ机器人推送
function boxmoe_msg_qq($qq, $msg)
{
	$message = "$msg";
	$time = "[检测时间:". date("y-m-d H:i:s")."]";
    $desp = $message . "\n".$time;
    // 封装，推送到 QQ
    $postdata = http_build_query(
        array(
            'message' => $desp
        )
	    );
// 执行POST请求
    $opts = array('http' =>
        array(
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );
    $context = stream_context_create($opts);  
    return $result = file_get_contents('http://127.0.0.1:5700/send_private_msg?user_id='.$qq.'', false, $context);
}

//开始监控
function startMonitor()
{
    global $config, $curl;
    $failedSites = [];
    foreach ($config['websites'] as $website) {
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 1,
            CURLOPT_URL => $website['url'],
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,

        ));
        $result = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($result === false || $code >= 400) {
            errLog(
                "[Web异常] " .
                "[监控网站]" . $website['url'] . " " .
                "[网站状态] $code " . curl_error($curl)
            );
            $failedSites[] = [
                "website" => $website,
                "err_msg" => curl_error($curl),
                "code" => $code,
            ];
        } else {
            infoLog(
                "[检测正常] [监控网站] " . $website['url']." [网站状态] ".$code
            );
        }
    }
    if (count($failedSites) && count($config['qq'])) {        
        $eMessage = "Web异常通知 \n";
        foreach ($failedSites as $i => $fail) {
            $eMessage .= "$i.\n";
            $eMessage .= "\t[监控节点] " . $fail['website']['title'] . "\n" .
                "\t[监控网站] " . $fail['website']['url'] . "\n" .
                "\t[异常状态] (" . $fail['code'] . ") " . $fail['err_msg'] . "\n";
        }
		//通知QQ推送异常消息
        foreach ($config['qq'] as $qq) {
            boxmoe_msg_qq($qq, $eMessage);
        }
    }
}
startMonitor();
curl_close($curl);
