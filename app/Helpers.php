<?php

function getLoginUser()
{
    $user = [];
    $user_token = '';

    if (Request()->isMethod('post')) {
        $user_token = Request()->user_token;
        if (empty($user_token)) $user_token = Request()->header('user_token');
    } else {
        $user_token = Cookie::get('user_token');
    }
    if (empty($user_token)) return $user;

    $user_login_log = DB::table('user_login_log')->where(['token' => $user_token, 'status' => 1])->first();
    if (empty($user_login_log)) return $user;
    $select = [
        'user.id',
        'user.wxmp_openid',
        'user.wxapp_openid',
        'user.nickname',
        'user.avatar',
        'user.sex',
        'user.phone',
        'user.email',
        'user.wallet',
        'user.gold',
        'user.city_id',
        'user.sex',
        'user.password',
        'user.realname_auth',
        'user.company_auth',
        'user.city_name'
    ];
    $user = DB::table('user')
        ->select($select)
        ->where('user.id', $user_login_log->user_id)
        ->where('user.status', 1)
        ->first();

    if (empty($user)) return $user;
    $user->avatar = !empty($user->avatar) ? fileView($user->avatar) : Config('common.image.user_avatar');

    // VIP会员
    $user->vip = 0;
    $user_member = DB::table('user_member')->where('user_id', $user->id)->orderBy('end_date', 'desc')->first();
    if (!empty($user_member)) {
        if (strtotime($user_member->end_date) > time()) $user->vip = 1;
        $user->member_end_date = date('Y-m-d', strtotime($user_member->end_date));
    }

    // 实名认证
    if ($user->realname_auth == 2) {
        $user->realname_auth_log = DB::table('user_realname_auth_log')->where(['user_id' => $user->id, 'status' => 2])->orderBy('created_at', 'desc')->first();
    }

    // 企业认证
    if ($user->company_auth == 2) {
        $user->company_auth_log = DB::table('user_company_auth_log')->where(['user_id' => $user->id, 'status' => 2])->orderBy('created_at', 'desc')->first();
    }

    return $user;
}

/**
 * 对象 转 数组
 * @param object $obj 对象
 * @return array
 */
function object_to_array($obj)
{
    if (empty($obj)) return [];
    $obj = (array)$obj;
    foreach($obj as $k => $v) {
        if (gettype($v) == 'resource') {
            return;
        }
        if (gettype($v) == 'object' || gettype($v) == 'array') {
            $obj[$k] = (array)object_to_array($v);
        }
    }
    return $obj;
}

/**
 * @param string $message
 * @param int $code
 * @return json
 */
function jsonFailed($message = 'operate failed', $code = 400)
{
    return response()->json(['code' => $code, 'message' => $message]);
}

/**
 * @param array $data
 * @param int $code
 * @return json
 */
function jsonSuccess($data = '', $code = 200, $message = 'operate success')
{
	return response()->json(['code' => $code, 'data' => $data, 'message' => $message]);
}

/**
 * @param string $message
 * @param int $code
 * @return array
 */
function arrayFailed($message = 'operate failed', $code = 400)
{
    return ['code'=>$code, 'message'=>$message];
}

/**
 * @param array $data
 * @param int $code
 * @return json
 */
function arraySuccess($data = '', $code = 200, $message = 'operate success')
{
	return ['code' => $code, 'data' => $data, 'message' => $message];
}

/**
 * Log Write
 * @param string $data
 * @param string $type
 */
function logWrite($data = '', $type = 'error')
{
    $path = storage_path('logs') . '/' . $type;
    if (!is_dir($path)) mkdir($path, 0777, TRUE);
    $file = $path . '/' . date('Ymd') . '.log';
    if (!is_file($file)) {
        touch($file);
        chmod($file, 0777);
    }
    $data = date('Y-m-d H:i:s') . "\n" . (is_array($data) ? serialize($data) : $data) . "\n\n";
    $handle = fopen($file, 'a');
	fwrite($handle, $data);
	fclose($handle);
}

/**
 * 文件输出格式
 * 阿里云oss控制
 */
function fileView($file = '')
{
    if (empty($file)) return $file;
    $file = Config('common.oss.status') ? Config('common.oss.url') . $file : Config('common.app_url') . $file;
    return $file;
}

/**
 * 统一数据库文件格式
 */
function fileFormat($file = '')
{
    if (empty($file)) return $file;
    if (strstr($file, Config('common.app_url'))) $file = str_replace(Config('common.app_url'), '', $file);
    if (!empty(Config('common.oss.url')) && strstr($file, Config('common.oss.url'))) $file = str_replace(Config('common.oss.url'), '', $file);
    return $file;
}

/**
 * 数组分组
 */
function arrayGroup($array, $key)
{
    $res = [];
    foreach($array as $k => $v){
        $res[$v[$key]][] = $v;
    }
    return $res;
}

/**
 * 数组排序
 * 数组根据某个键值排序
 * @param array $array
 * @param string $param_key 键值
 * @param string asc|desc
 * @return array
 */
function arraySort($array, $param_key, $sort = 'asc')
{
    if (empty($array)) return $array;
    $newArr = $valArr = array();
    foreach ($array as $key=>$value) {
        $valArr[$key] = $value[$param_key];
    }
    $sort == 'asc' ?  asort($valArr) : arsort($valArr);
    reset($valArr);
    foreach ($valArr as $key => $value) {
        $newArr[$key] = $array[$key];
    }
    return $newArr;
}

function curl_get($url, $header = [])
{
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	if ($header) curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
}

function curl_post($url, $params = '', $header = [])
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    if ($params) {
        if (is_array($params)) {
            $str = "";
            foreach ($params as $key => $value) {
                $str .= $key.'='.urlencode($value)."&";
            }
            $str = substr($str, 0, -1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $str);
        } else {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        }
    }
    if ($header) curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
}

/**
 * 下载远程图片到本地
 * @param string url 远程图片url
 * @param string 本地保存路径
 * @param string 本地保存名字
 * @return string
 */
function download_image($url, $path, $file_name = '')
{
	if (!is_dir($path)) mkdir($path, 0777, true);
	if (!is_readable($path)) chmod($path, 0777, true);

	if (empty($file_name)) $file_name = md5(time()) . '.png';
	$file_name = $path . $file_name;

	ob_start();
    readfile($url);
    $content = ob_get_contents();
    ob_end_clean();
	$size = strlen($content);

	$fp = @fopen($file_name, "a");
    fwrite($fp, $content);
	fclose($fp);

	return $file_name;
}

/**
 * 通过二维数组中的某个值，获取某一列集合
 * @param array $array 必需为二维数组
 */
function getColumnArray($param_key, $param_value, $array)
{
    $data = [];
    foreach ($array as $key => $value) {
        if ($param_value == $value[$param_key]) $data = $value;
    }
    return $data;
}

// 写入配置文件
function writeConfigFile($array, $file_name)
{
    $data = '';
    $data = "<?php\n\n";
    $data .= 'return ';
    $data .= '\'';
    $data .= !empty($array) ? json_encode($array, 1) : '';
    $data .= '\'';
    $data .= ';';

    $readfile_path = config_path() . '/readfile';
    if (!file_exists($readfile_path)) mkdir($readfile_path, 0777);
    $path = $readfile_path . '/' . $file_name;
    $file = fopen($path, "w+");
    fwrite($file, $data);
    fclose($file);
}

function randStr($length = 12)
{
    //$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghigklmnopqrstuvwxyz0123456789';
    $chars = 'abcdefghigklmnopqrstuvwxyz0123456789';
    $str = '';
    for ($i = 0; $i < $length; $i++) {
        $position = rand() % strlen($chars);
        $str .= substr($chars, $position, 1);
    }
    return $str;
}

// 编码上一页参数
function encodePrevPageParams($connect_symbol = '&')
{
    $str = '';
    if (Request()->all()) {
        $str = $connect_symbol . 'prevPageParams=' . urlencode(http_build_query(Request()->all()));
    }
    return $str;
}

// 解析上一页参数
function decodePrevPageParams()
{
    $str = '';
    if (Request()->get("prevPageParams")) {
        $str = '?' . urldecode(Request()->get("prevPageParams"));
    }
    return $str;
}

function getSpu()
{
    return strtoupper(substr(md5(uniqid() . microtime() . rand(1000, 9999)), 0, 12));
    // $max = 100;
    // $i = 0;
    // while ($i < $max) {
    //     $spu = time();
    //     $exists = DB::table('product')->where('spu', $spu)->exists();
    //     if (!$exists) {
    //         return $spu;
    //     }
    // }
    // return jsonFailed('已达到最大尝试次数');
}

function getSku()
{
    return strtoupper(substr(md5(uniqid() . microtime() . rand(1000, 9999)), 0, 12));
    // $max = 100;
    // $i = 0;
    // while ($i < $max) {
    //     $sku = time();
    //     $exists = DB::table('product_sku')->where('sku', $sku)->exists();
    //     if (!$exists) {
    //         return $sku;
    //     }
    // }
    // return jsonFailed('已达到最大尝试次数');
}

function createOrderNumber($orderId)
{
    $number = time() . $orderId;
    return $number;
}

use App\Repositorys\ArticleRepository;

function get_list($params = [])
{
    $articles = app(ArticleRepository::class)->getArticles($params);
    return $articles;
}


/**
 * 设置 URL 参数
 * @param array $params
 * @return string
 */
function setUrlParams($params, $url = '')
{
	$parse_url = $url === '' ? parse_url($_SERVER["REQUEST_URI"]) : parse_url($url);
	$query = isset($parse_url['query']) ? $parse_url['query'] : '';
	$querys = explode('&', $query);
	$current_params = [];
	if ($querys[0] !== '') {
		foreach ($querys as $param){
			list($name, $value) = explode('=', $param);
			$current_params[urldecode($name)] = urldecode($value);
		}
	}
	foreach ($params as $key => $value) {
		$current_params[$key] = $value;
	}
	return $parse_url['path'].'?'.http_build_query($current_params);
}

/**
 * 删除文件夹/文件
 * @param string $path 文件夹/文件路径
 * @return float
 */
function deleteFile($path)
{
	// 文件
	if (!is_dir($path)) {
		if (unlink($path)) {
			return true;
		} else {
			return false;
		}
	}
	// 文件夹
	$dh = opendir($path);
	while ($file = readdir($dh)) {
		if ($file != "." && $file != "..") {
			$fullpath = $path . "/" . $file;
			if (!is_dir($fullpath)) {
				unlink($fullpath);
			} else {
				deleteFile($fullpath);
			}
		}
	}
	closedir($dh);
	if (rmdir($path)) {
		return true;
	} else {
		return false;
	}
}

