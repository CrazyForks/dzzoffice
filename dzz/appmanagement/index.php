<?php
/* @authorcode  codestrings
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      qchlian(3580164@qq.com)
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
$navtitle = lang('后台管理');
//管理权限进入
Hook::listen('adminlogin');
$do = isset($_GET['do']) ? $_GET['do'] : '';
if ($do == 'stats') {
    $starttime = trim($_GET['starttime']);
    $endtime = trim($_GET['endtime']);
    $time = trim($_GET['time']) ? trim($_GET['time']) : 'day';
    $operation = trim($_GET['operation']);
    switch ($time) {
        case 'month':
            if (!$starttime) {
                $start = strtotime("-6 month", TIMESTAMP);
                $starttime = dgmdate($start, 'Y-m');
            }
            if (!$endtime) {
                $endtime = dgmdate(TIMESTAMP, 'Y-m');
            }
            break;
        case 'week':
            if (!$starttime) {
                $start = strtotime("-12 week", TIMESTAMP);
            } else {
                $start = strtotime($starttime);
            }
            $stamp_l = strtotime("this Monday", $start);
            $starttime = dgmdate($stamp_l, 'Y-m-d');

            if (!$endtime) {
                $end = TIMESTAMP;
            } else {
                $end = strtotime($endtime);
            }
            $endtime = dgmdate($end, 'Y-m-d');
            break;
        case 'day':
            if (!$starttime) {
                $start = strtotime("-12 day", TIMESTAMP);
                $starttime = dgmdate($start, 'Y-m-d');
            }
            if (!$endtime) {
                $endtime = dgmdate(TIMESTAMP, 'Y-m-d');
            }
            break;

    }
    if ($operation == 'getdata') {
        $data = getData($time, $starttime, $endtime);
        // 构建返回的数据
        $response = [
            'success' => true,
            'labels' => array_keys($data['total']),
            'datasets' => [
                [
                    'label' => "用户总数",
                    'backgroundColor' => "#33cabb",
                    'borderColor' => "#33cabb",
                    'fill' => false,
                    'data' => array_values($data['total'])
                ],
                [
                    'label' => '新增用户',
                    'fill' => false,
                    'backgroundColor' => "#fa8734",
                    'borderColor' => "#fa8734",
                    'data' => array_values($data['add'])
                ]
            ]
        ];
        // 返回JSON数据
        exit(json_encode($response));
    } else {
        include template('stats');
        exit();
    }
} elseif ($do == 'systemcheck') {
    define('ROOT_PATH', dirname(__FILE__));
    $filesock_items = array('fsockopen', 'pfsockopen', 'stream_socket_client', 'mysqli_connect', 'file_get_contents', 'xml_parser_create', 'filesize', 'curl_init', 'zip_open', 'mb_check_encoding', 'mb_convert_encoding');
    $func_strextra = '';
    foreach ($filesock_items as $item) {
        $status = function_exists($item);
        $func_strextra .= "<tr>\n";
        $func_strextra .= "<td>$item()</td>\n";
        if ($status) {
            $func_strextra .= "<td class=\"text-success\"><i class=\"mdi lead mdi-check-circle me-2\"></i>" . lang('supportted') . "</td>\n";
            $func_strextra .= "<td>" . lang('none') . "</td>\n";
        } else {
            $func_strextra .= "<td class=\"nw text-danger\"><i class=\"mdi lead mdi-close-circle me-2\"></i>" . lang('unsupportted') . "</td>\n";
            $func_strextra .= "<td><font color=\"red\">" . lang('advice_' . $item) . "</font></td>\n";
        }
    }
    $env_items = array
    (
        '操作系统' => array('c' => 'PHP_OS', 'r' => '不限制', 'b' => 'Linux'),
        'PHP 版本' => array('c' => 'PHP_VERSION', 'r' => '7+', 'b' => 'php7+'),
        'PHP 平台版本' => array('c' => 'PHP_INT_SIZE', 'r' => '32位(32位不支持2G以上文件上传下载)', 'b' => '64位'),
        '最大上传大小' => array('r' => '不限制', 'b' => '50M'),
        '最大post大小' => array('r' => '不限制', 'b' => '50M'),
        '最大内存限制' => array('r' => '不限制', 'b' => '128M'),
        'GD 库' => array('r' => '1.0', 'b' => '2.0'),
        '磁盘空间' => array('r' => '50M', 'b' => '10G以上'),
        'MySQL数据库持续连接' => array('r' => '不限制', 'b' => '不限制'),
        '域名' => array('r' => '不限制', 'b' => '不限制'),
        '服务器端口' => array('r' => '不限制', 'b' => '不限制'),
        '运行环境' => array('r' => '不限制', 'b' => 'nginx'),
        '网站根目录' => array('r' => '不限制', 'b' => '不限制'),
        '执行时间限制' => array('r' => '不限制', 'b' => '不限制'),
    );
    foreach ($env_items as $key => $item) {
        if ($key == 'PHP 版本') {
            $env_items[$key]['current'] = PHP_VERSION;
        } elseif ($key == 'PHP 平台版本') {
            $env_items[$key]['current'] = phpBuild64() ? 64 : 32;
        } elseif ($key == '最大上传大小') {
            $env_items[$key]['current'] = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknownn';
        } elseif ($key == '最大内存限制') {
            $env_items[$key]['current'] = ini_get('memory_limit') ?? 'unknown';
        } elseif ($key == '最大post大小') {
            $env_items[$key]['current'] = ini_get('post_max_size') ?? 'unknown';
        } elseif ($key == 'allow_url_fopen') {
            $env_items[$key]['current'] = @ini_get('allow_url_fopen') ? ini_get('allow_url_fopen') : 'unknown';
        } elseif ($key == 'GD 库') {
            $tmp = function_exists('gd_info') ? gd_info() : array();
            $env_items[$key]['current'] = empty($tmp['GD Version']) ? 'noext' : $tmp['GD Version'];
            unset($tmp);
        } elseif ($key == '磁盘空间') {
            if (function_exists('disk_free_space')) {
                $env_items[$key]['current'] = floor(disk_free_space(ROOT_PATH) / (1024 * 1024)) . 'M';
            } else {
                $env_items[$key]['current'] = 'unknown';
            }
        } elseif ($key == 'PHP 平台版本') {
            if (PHP_INT_SIZE === 4) {
                $env_items[$key]['current'] = '32位';
            } else if (PHP_INT_SIZE === 8) {
                $env_items[$key]['current'] = '64位';
            } else {
                $env_items[$key]['current'] = '无法确定架构类型';
            }
        } elseif ($key == 'MySQL数据库持续连接') {
            $env_items[$key]['current'] = @get_cfg_var("mysql.allow_persistent") ? "是 " : "否";
        } elseif ($key == '域名') {
            $env_items[$key]['current'] = GetHostByName($_SERVER['SERVER_NAME']);
        } elseif ($key == '服务器端口') {
            $env_items[$key]['current'] = $_SERVER['SERVER_PORT'];
        } elseif ($key == '运行环境') {
            $env_items[$key]['current'] = $_SERVER["SERVER_SOFTWARE"];
        } elseif ($key == '网站根目录') {
            $env_items[$key]['current'] = $_SERVER["DOCUMENT_ROOT"];
        } elseif ($key == '执行时间限制') {
            $env_items[$key]['current'] = ini_get('max_execution_time') . '秒';
        } elseif (isset($item['c'])) {
            $env_items[$key]['current'] = constant($item['c']);
        }

        $env_items[$key]['status'] = 1;
        if ($item['r'] != 'notset' && strcmp($env_items[$key]['current'], $item['r']) < 0) {
            $env_items[$key]['status'] = 0;
        }
    }
    $env_str = '';
    foreach ($env_items as $key => $item) {
        $status = 1;
        $env_str .= "<tr>\n";
        $env_str .= "<td>$key</td>\n";
        $env_str .= "<td>$item[r]</td>\n";
        $env_str .= "<td>$item[b]</td>\n";
        $env_str .= ($status ? "<td class=\"text-success\"><i class=\"mdi lead mdi-check-circle me-2\"></i>" : "<td class=\"nw text-danger\"><i class=\"mdi lead mdi-close-circle me-2\"></i>") . $item['current'] . "</td>\n";
        $env_str .= "</tr>\n";
    }
    include template('systemcheck');
    exit();
} elseif ($do == 'phpinfo') {
    exit(phpinfo());
} elseif ($do == 'online') {
    $bodyClass = 'bg-body';
    include template('online');
    exit();
} elseif ($do == 'onlineinfo') {
    $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
    $ismember = isset($_GET['ismember']) ? trim($_GET['ismember']) : '';
    $field = isset($_GET['field']) ? $_GET['field'] : 'lastactivity';
    $limit = empty($_GET['limit']) ? 20 : $_GET['limit'];
    $page = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
    $start = ($page - 1) * $limit;
    $validfields = ['sid','uid', 'groupid','ip','lastactivity','lastolupdate'];
    $validSortOrders = ['asc', 'desc'];
    if (in_array($field, $validfields) && in_array($order, $validSortOrders)) {
        $order = "ORDER BY $field $order";
    } else {
        $order = 'ORDER BY lastactivity DESC';
    }
	$onlinedata = $list = array();
    $sql = '1';
    if ($ismember == 1) {
        $sql .= ' and uid > 0';
    } elseif ($ismember == 2) {
        $sql .= ' and uid = 0';
    }
    $param = array('session');
    $limitsql = 'limit ' . $start . ',' . $limit;
    if ($count = DB::result_first("SELECT COUNT(*) FROM %t WHERE $sql ", $param)) {
       $onlinedata = DB::fetch_all("SELECT * FROM %t WHERE $sql $order $limitsql", $param);
    }
    if ($onlinedata) {
        $usergroup = array();
        foreach (C::t('usergroup')->range() as $group) {
            $usergroup[$group['groupid']] = $group['grouptitle'];
        }
        foreach ($onlinedata as $value) {
            if(!$value['username']) {
                $value['username'] = lang('anonymous');
            }
            $list[] = [
                "uid" => $value['uid'] ? '<a href="'.USERSCRIPT.'?uid='.$value['uid'].'" target="_blank">'.avatar_block($value['uid']).$value['username'].'</a>' : '游客',
                "groupid" => $usergroup[$value['groupid']],
                "sid" => $value['sid'],
                "ip" => $value['ip'],
                "lastactivity" => $value['lastactivity'] ? dgmdate($value['lastactivity'],'u') : '',
                "lastolupdate" => $value['lastolupdate'] ? dgmdate($value['lastolupdate'],'u') : ''
            ];
        }
    }
    header('Content-Type: application/json');
    $return = [
        "code" => 0,
        "msg" => "",
        "count" => $count ? $count : 0,
        "data" => $list ? $list : []
    ];
    $jsonReturn = json_encode($return);
    if ($jsonReturn === false) {
        $errorMessage = json_last_error_msg();
        $errorResponse = [
            "code" => 1,
            "msg" => "JSON 编码失败，请刷新重试: " . $errorMessage,
            "count" => 0,
            "data" => [],
        ];
        exit(json_encode($errorResponse));
    }
    exit($jsonReturn);
}
$appdata = DB::fetch_all("select appid,appname,appico,appurl,identifier,appadminurl from %t where ((`group`=3 and isshow>0) OR appadminurl!='')  and `available`>0 order by appid",array('app_market'));
$data = array();
foreach ($appdata as $k => $v) {
    if ($v["identifier"] == "appmanagement") continue;
    if ($v['appico'] != 'dzz/images/default/icodefault.png' && !preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $v['appico'])) {
        $v['appico'] = $_G['setting']['attachurl'] . $v['appico'];
    }
    if($v['group'] == 3) {
        $v['url'] = replace_canshu($v['appurl']);
    } else {
        $v['url']=$v['appadminurl']?replace_canshu($v['appadminurl']):replace_canshu($v['appurl']);
    }
    $data[] = $v;
}
$yonghurenshu = DB::result_first("SELECT COUNT(*) FROM " . DB::table('user') . " WHERE uid");
$tingyongrenshu = DB::result_first("SELECT COUNT(*) FROM " . DB::table('user') . " WHERE status");
$wenjiangeshu = DB::result_first("SELECT COUNT(*) FROM " . DB::table('attachment') . " WHERE aid");
$kongjianshiyong = formatsize(DB::result_first("SELECT SUM(filesize) FROM " . DB::table('attachment')));
$version = 'V' . CORE_VERSION;//版本信息
$RELEASE = CORE_RELEASE;
include template('main');
function phpBuild64() {
    if (PHP_INT_SIZE === 8) return true;//部分版本,64位会返回4;
    ob_clean();
    ob_start();
    var_dump(12345678900);
    $res = ob_get_clean();
    if (strstr($res, 'float')) return false;
    return true;
}

function getData($time, $starttime, $endtime) {
    $endtime = strtotime($endtime);
    $data = array('total' => array(),
        'add' => array(),
        'total_d' => array(),
        'add_d' => array(),
    );
    switch ($time) {
        case 'month':
            $stamp = strtotime($starttime);
            $arr = getdate($stamp);
            $key = $arr['year'] . '-' . $arr['mon'];
            $low = strtotime($key);
            $up = strtotime('+1 month', $low);
            $ltotal = $data['total'][$key] = DB::result_first("select COUNT(*) from %t where regdate<%d", array('user', $up));
            $data['add'][$key] = DB::result_first("select COUNT(*) from %t where regdate<%d and regdate>=%d", array('user', $up, $low));
            $ltotal += $data['add'][$key];
            while ($up <= $endtime) {
                $key = dgmdate($up, 'Y-m');
                $low = strtotime($key);
                $up = strtotime('+1 month', $low);
                $data['add'][$key] = DB::result_first("select COUNT(*) from %t where regdate<%d and regdate>=%d", array('user', $up, $low));
                $ltotal += $data['add'][$key];
                $data['total'][$key] = $ltotal;
            }
            break;
        case 'week':
            $stamp = strtotime($starttime);
            $arr = getdate($stamp);
            $low = strtotime('+' . (1 - $arr['wday']) . ' day', $stamp);
            $up = strtotime('+1 week', $low);
            $key = dgmdate($low, 'm-d') . '~' . dgmdate($up - 60 * 60 * 24, 'm-d');
            $ltotal = $data['total'][$key] = DB::result_first("select COUNT(*) from %t where regdate<%d", array('user', $up));
            $data['add'][$key] = DB::result_first("select COUNT(*) from %t where regdate<%d and regdate>=%d", array('user', $up, $low));
            $ltotal += $data['add'][$key];
            while ($up < $endtime) {
                $low = $up;
                $up = strtotime('+1 week', $low);
                $key = dgmdate($low, 'm-d') . '~' . dgmdate($up - 60 * 60 * 24, 'm-d');
                $data['add'][$key] = DB::result_first("select COUNT(*) from %t where regdate<%d and regdate>=%d", array('user', $up, $low));
                $ltotal += $data['add'][$key];
                $data['total'][$key] = $ltotal;
            }
            break;
        case 'day':
            $low = strtotime($starttime);//strtotime('+'.(1-$arr['hours']).' day',$stamp);
            $up = $low + 24 * 60 * 60;
            $key = dgmdate($low, 'Y-m-d');
            $ltotal = $data['total'][$key] = DB::result_first("select COUNT(*) from %t where regdate<%d", array('user', $up));
            $data['add'][$key] = DB::result_first("select COUNT(*) from %t where regdate<%d and regdate>=%d", array('user', $up, $low));
            $ltotal += $data['add'][$key];
            while ($up <= $endtime) {
                $low = $up;
                $up = strtotime('+1 day', $low);
                $key = dgmdate($low, 'Y-m-d');
                $data['add'][$key] = DB::result_first("select COUNT(*) from %t where regdate<%d and regdate>=%d", array('user', $up, $low));
                $ltotal += $data['add'][$key];
                $data['total'][$key] = $ltotal;
            }
            break;
        case 'all':
            $min = DB::result_first("select min(regdate) from %t where regdate>0", array('user'));
            $min -= 60;
            $max = TIMESTAMP + 60 * 60 * 8;
            $days = ($max - $min) / (60 * 60 * 24);
            if ($days < 20) {
                $time = 'day';
                $starttime = gmdate('Y-m-d', $min);
                $endtime = gmdate('Y-m-d', $max);
            } elseif ($days < 70) {
                $time = 'week';
                $starttime = gmdate('Y-m-d', $min);
                $endtime = gmdate('Y-m-d', $max);
            } else {
                $time = 'month';
                $starttime = gmdate('Y-m', $min);
                $endtime = gmdate('Y-m', $max);
            }
            $data = getData($time, $starttime, $endtime);
            break;
    }
    return $data;
}