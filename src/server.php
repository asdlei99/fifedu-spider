<?php
use GuzzleHttp\Client;

// 开启全部报错
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ALL);

// Composer自动加载
require '../vendor/autoload.php';

/**
 * 获取配置参数
 *
 * @param string $key
 *
 * @return mixed
 */
function config($key)
{
    static $config = null;
    if (is_null($config)) {
        $config = include 'config.php';
    }
    return $config[$key];
}

/**
 * 记录日志
 *
 * @param string $str 一行日志
 */
function log_record($str)
{
    $output = $str . "\n";
    echo $output;
    file_put_contents(config('log_file'), $output, FILE_APPEND);
}

/**
 * 封装preg_match_all正则匹配
 *
 * @param string $pattern 正则表达式
 * @param string $subject 文本
 *
 * @return mixed PREG_SET_ORDER的结果
 */
function &match_all($pattern, $subject)
{
    preg_match_all($pattern, $subject, $matches, PREG_SET_ORDER);
    return $matches;
}

/**
 * 获取毫秒时间戳
 *
 * @return string
 */
function get_timestamp()
{
    return substr(str_replace('.', '', (string)microtime(true)), 0, -1);
}

/**
 * 伪造一个jQuery jsonp callback的函数名
 *
 * @return string
 */
function generate_callback()
{
    return 'jQuery191' . sprintf('%09d', mt_rand(0, 999999999)) . sprintf('%09d', mt_rand(0, 999999999)) . '_' . get_timestamp();
}

// 新建Client
$client = new Client([
    'cookies' => true,
    'verify' => false,
    // 'proxy' => '127.0.0.1:8888',
]);

// 连接数据库
$mysqli = new mysqli(config('database')['host'], config('database')['username'], config('database')['password'], config('database')['database'], config('database')['port']);
$mysqli->set_charset(config('database')['charset']);

// 目标网址
$url = [
    'teacher' => 'http://www.fifedu.com/iplat/bp/member/teacherList?curPage=',
    'student' => 'http://www.fifedu.com/iplat/bp/member/studentList?curPage=',
    'class1' => 'http://www.fifedu.com/iplat/bp/class/classList?classType=01&curPage=',
    'class2' => 'http://www.fifedu.com/iplat/bp/class/classList?classType=02&curPage=',
    'class1_student' => ['http://www.fifedu.com/iplat/bp/class/studentList?classId=', '&curPage='],
    'class2_student' => ['http://www.fifedu.com/iplat/bp/class/studentList?classId=', '&curPage='],
    'college' => 'http://www.fifedu.com/iplat/bp/college/list?&curPage=',
    'college_class1' => ['http://www.fifedu.com/iplat/bp/college/classList?collegeId=', '&classType=01&curPage='],
];

// 用于匹配的正则表达式
$regexp = [
    'teacher' => '/<tr>\\s*?<td>(.*?)<input type="hidden" value=".*?"><\\/td>\\s*?<td class="table_td-left"><p class="huoban-name" title="(.*?)">.*?<\\/td>\\s*?<td class="table_td-left"><p title="(.*?)">.*?<\\/p><\\/td>\\s*?<td class="table_td-left"><p title="(.*?)">.*?<\\/p><\\/td>\\s*?<td class="table_td-left"><p title="(.*?)">.*?<\\/p><\\/td>[\\s\\S]*?setRole\\(\'(.*?)\'/',
    'student' => '/<tr>\\s*?<td>(.*?)<input type="hidden" value=".*?"><\\/td>\\s*?<td class="table_td-left"><p class="huoban-name" title="(.*?)">.*?<\\/td>\\s*?<td class="table_td-left"><p title="(.*?)">.*?<\\/p><\\/td>\\s*?<td class="table_td-left"><p title="(.*?)">.*?<\\/p><\\/td>\\s*?<td class="table_td-left"><p title="(.*?)">.*?<\\/p><\\/td>\\s*?<td title="(.*?)">.*?<\\/td>\\s*?<td class="table_td-left"><p title="(.*?)">.*?<\\/p><\\/td>/',
    'class1' => '/<tr>\\s*?<td><input type="hidden" value="(.*?)">(.*?)<\\/td>\\s*?<td class="table_td-left"><p title="(.*?)">.*?<\\/p><\\/td>\\s*?<td>\\s*?(\\S*?)\\s*?<\\/td>\\s*?<td class="table_td-left"><p title="(.*?)">.*?<\\/p><\\/td>\\s*?<td>(.*?)<\\/td>/',
    'class2' => '/<tr>\\s*?<td><input type="hidden" value="(.*?)">(.*?)<\\/td>\\s*?<td class="table_td-left"><p title="(.*?)">.*?<\\/p><\\/td>\\s*?<td>\\s*?(\\S*?)\\s*?<\\/td>\\s*?<td class="table_td-left"><p title="(.*?)">.*?<\\/p><\\/td>\\s*?<td>(.*?)<\\/td>\\s*?<td>(.*?)<\\/td>[\\s\\S]*?teacherid="(.*?)"/',
    'class1_student' => '/<tr>\\s*?<td>.*?<input type="hidden" value=".*?"><\\/td>\\s*?<td class="table_td-left"><p class="huoban-name" title="">.*?<\\/p><\\/t d>\\s*?<td>(.*?)<\\/td>[\\s\\S]*?queryTargetInfo\\(\'(.*?)\'/',
    'class2_student' => '/<tr>\\s*?<td>.*?<input type="hidden" value=".*?"><\\/td>\\s*?<td class="table_td-left"><p class="huoban-name" title="">.*?<\\/p><\\/t d>\\s*?<td>(.*?)<\\/td>/',
    'college' => '/<tr>\\s*?<td>(\\S*?)\\s*?<input type="hidden" name="" value=".*?"><\\/td>\\s*?<td>(.*?)<\\/td>\\s*?<td>(.*?)<\\/td>[\\s\\S]*?queryTargetInfo\\(\'(.*?)\'/',
    'college_class1' => '/<tr>\\s*?<td>.*?<\\/td>\\s*?<td>(.*?)<\\/td>\\s*?<td>(.*?)级<\\/td>/',
];

// SQL prepare
$stmt = [
    'teacher' => $mysqli->prepare('INSERT INTO `fifedu_teacher` (`id`, `name`, `username`, `card_num`, `college_name`, `uid`) VALUES (?, ?, ?, ?, ?, ?)'),
    'student' => $mysqli->prepare('INSERT INTO `fifedu_student` (`id`, `name`, `username`, `card_num`, `class1_name`, `grade`, `college_name`) VALUES (?, ?, ?, ?, ?, ?, ?)'),
    'class1' => $mysqli->prepare('INSERT INTO `fifedu_class1` (`uid`, `id`, `name`, `grade`, `college_name`, `student_count`) VALUES (?, ?, ?, ?, ?, ?)'),
    'class2' => $mysqli->prepare('INSERT INTO `fifedu_class2` (`uid`, `id`, `name`, `grade`, `college_name`, `teacher_name`, `student_count`, `teacher_uid`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'),
    'class1_student' => $mysqli->prepare('UPDATE `fifedu_student` SET `uid` = ?, `class1_uid` = ? WHERE `username` = ?'),
    'class2_student' => $mysqli->prepare('UPDATE `fifedu_student` SET `class2_uid` = ?, `class2_name` = ? WHERE `username` = ?'),
    'college' => $mysqli->prepare('INSERT INTO `fifedu_college` (`id`, `name`, `class1_count`, `uid`) VALUES (?, ?, ?, ?)'),
];

// SQL参数绑定
$stmt['teacher']->bind_param("dsssss", $id, $name, $username, $card_num, $college_name, $uid);
$stmt['student']->bind_param("dssssss", $id, $name, $username, $card_num, $class1_name, $grade, $college_name);
$stmt['class1']->bind_param("sdssss", $uid, $id, $name, $grade, $college_name, $student_count);
$stmt['class2']->bind_param("sdssssss", $uid, $id, $name, $grade, $college_name, $teacher_name, $student_count, $teacher_uid);
$stmt['class1_student']->bind_param("sss", $student_uid, $class1_uid, $username);
$stmt['class2_student']->bind_param("sss", $class2_uid, $class2_name, $username);
$stmt['college']->bind_param("dsss", $id, $name, $class1_count, $uid);

// 登录
$jsonp = $client->get('http://cycore.fifedu.com/sso/login?service=http%3A%2F%2Fwww.fifedu.com%2Fiplat%2Fssoservice&callback=' . generate_callback() . '&_=' . get_timestamp())->getBody();
preg_match('/{"lt" : "(.*?)", "execution" : "(.*?)"/', $jsonp, $matches);
$client->get('http://cycore.fifedu.com/sso/login?service=http%3A%2F%2Fwww.fifedu.com%2Fiplat%2Fssoservice&callback=' . generate_callback() . '&username=' . config('fifedu')['username'] . '&password=' . config('fifedu')['password'] . '&sourceappname=iflyportal%2Ciflyportal&key=login_name&_eventId=submit&lt=' . $matches[1] . '&execution=' . $matches[2] . '&_=' . get_timestamp())->getBody();

// ========================================
//
//                 开始拖库
//
// ========================================

log_record('==================== 教师信息 ====================');
for ($i = 1; ; ++$i) {
    $html = $client->get($url['teacher'] . $i)->getBody();
    if (!$matches = match_all($regexp['teacher'], $html)) {
        break;
    }
    log_record('>>>>>>>>>> TEACHER PAGE: ' . $i);
    foreach ($matches as &$val) {
        $id = $val[1];
        $name = $val[2];
        $username = $val[3];
        $card_num = $val[4];
        $college_name = $val[5];
        $uid = $val[6];
        $stmt['teacher']->execute();
        unset($val[0]);
        log_record(implode("\t", $val));
    }
}

log_record('==================== 学生信息 ====================');
for ($i = 1; ; ++$i) {
    $html = $client->get($url['student'] . $i)->getBody();
    if (!$matches = match_all($regexp['student'], $html)) {
        break;
    }
    log_record('>>>>>>>>>> STUDENT PAGE: ' . $i);
    foreach ($matches as &$val) {
        $id = $val[1];
        $name = $val[2];
        $username = $val[3];
        $card_num = $val[4];
        $class1_name = $val[5];
        $grade = $val[6];
        $college_name = $val[7];
        $stmt['student']->execute();
        unset($val[0]);
        log_record(implode("\t", $val));
    }
}

log_record('==================== 自然班信息 ====================');
for ($i = 1; ; ++$i) {
    $html = $client->get($url['class1'] . $i)->getBody();
    if (!$matches = match_all($regexp['class1'], $html)) {
        break;
    }
    log_record('>>>>>>>>>> CLASS1 PAGE: ' . $i);
    foreach ($matches as &$val) {
        $uid = $val[1];
        $id = $val[2];
        $name = $val[3];
        $grade = $val[4];
        $college_name = $val[5];
        $student_count = $val[6];
        $stmt['class1']->execute();
        unset($val[0]);
        log_record(implode("\t", $val));
        for ($j = 1; ; ++$j) {
            $html = $client->get($url['class1_student'][0] . $uid . $url['class1_student'][1] . $j)->getBody();
            if (!$matches2 = match_all($regexp['class1_student'], $html)) {
                break;
            }
            log_record('----- CLASS1 ' . $name . ' STUDENT PAGE: ' . $j);
            foreach ($matches2 as &$val2) {
                $student_uid = $val2[2];
                $class1_uid = $uid;
                $username = $val2[1];
                $stmt['class1_student']->execute();
                unset($val2[0]);
                log_record(implode("\t", $val2));
            }
        }
    }
}

log_record('==================== 教学班信息 ====================');
for ($i = 1; ; ++$i) {
    $html = $client->get($url['class2'] . $i)->getBody();
    if (!$matches = match_all($regexp['class2'], $html)) {
        break;
    }
    log_record('>>>>>>>>>> CLASS2 PAGE: ' . $i);
    foreach ($matches as &$val) {
        $uid = $val[1];
        $id = $val[2];
        $name = $val[3];
        $grade = $val[4];
        $college_name = $val[5];
        $teacher_name = $val[6];
        $student_count = $val[7];
        $teacher_uid = $val[8];
        $stmt['class2']->execute();
        unset($val[0]);
        log_record(implode("\t", $val));
        for ($j = 1; ; ++$j) {
            $html = $client->get($url['class2_student'][0] . $uid . $url['class2_student'][1] . $j)->getBody();
            if (!$matches2 = match_all($regexp['class2_student'], $html)) {
                break;
            }
            log_record('----- CLASS2 ' . $name . ' STUDENT PAGE: ' . $j);
            foreach ($matches2 as &$val2) {
                $class2_uid = $uid;
                $class2_name = $name;
                $username = $val2[1];
                $stmt['class2_student']->execute();
                unset($val2[0]);
                log_record(implode("\t", $val2));
            }
        }
    }
}
*/
log_record('==================== 院系信息 ====================');
for ($i = 1; ; ++$i) {
    $html = $client->get($url['college'] . $i)->getBody();
    if (!$matches = match_all($regexp['college'], $html)) {
        break;
    }
    log_record('>>>>>>>>>> COLLEGE PAGE: ' . $i);
    foreach ($matches as &$val) {
        $id = $val[1];
        $name = $val[2];
        $class1_count = $val[3];
        $uid = $val[4];
        $stmt['college']->execute();
        unset($val[0]);
        log_record(implode("\t", $val));
        for ($j = 1; ; ++$j) {
            $html = $client->get($url['college_class1'][0] . $uid . $url['college_class1'][1] . $j)->getBody();
            if (!$matches2 = match_all($regexp['college_class1'], $html)) {
                break;
            }
            log_record('----- COLLEGE ' . $name . ' CLASS1 PAGE: ' . $j);
            foreach ($matches2 as &$val2) {
                // TODO
                unset($val2[0]);
                log_record(implode("\t", $val2));
            }
        }
    }
}

// 关闭数据库连接
$mysqli->close();
