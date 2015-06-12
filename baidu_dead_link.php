<?php
/**
	* 功能: 记录百度抓取的死链，用于提交到百度，方便百度尽快删除收录的死链接
	* 作者：Rain
	* QQ：  563268276
	* 推荐站点
	* www.itziy.com pudn免积分下载器  51cto免积分下载器
	* www.94cto.com 免费分享编程开发相关的资源包括电子书、视频教程、文档手册、技术文章、精品源码、开发工具
	* www.verypan.com 百度网盘搜索引擎
*/
define('OUTPUT_FILE', 'baidu_dead_link.txt');
define('SLEEP_TIME', 1);
if ($argc != 3)
{
	echo '********************************************************************',PHP_EOL;
	echo '* usage',PHP_EOL;
	echo '* php baidu_dead_link.php SITE_URL END_PAGE_NUMBER',PHP_EOL;
	echo '* example',PHP_EOL;
	echo '# php baidu_dead_link.php www.qq.com 10',PHP_EOL;
	echo '********************************************************************',PHP_EOL;
}

if (file_exists(OUTPUT_FILE))
	unlink(OUTPUT_FILE);

$site_url = $argv[1];
$end_page = (int)$argv[2];

$start = 0;
while ($start < $end_page)
{
	$check_url = 'http://www.baidu.com/s?wd=site%3A'.$site_url.'&pn='.($start * 10).'&oq=site%3A'.$site_url.'&cl=0&ie=utf-8';
	echo 'start to check baidu url: ',$check_url,PHP_EOL,PHP_EOL;
	$ret = get($check_url);
	$urlArr = parse($ret);
	if (!$urlArr)
	{
		$start++;
		echo 'get result link url error',PHP_EOL;
		continue;
	}
	foreach ($urlArr as $url)
	{
		$ret = get($url);
		preg_match("/Location:\s(.*?)\r\n/i", $ret, $match);
		//echo 'check location url: ',$match[1],PHP_EOL;
		$headArr = get_headers($match[1]);
		if (stripos($headArr[0], '200 OK') === false)
		{
			echo '* find dead link: ',$match[1],PHP_EOL,PHP_EOL;
			file_put_contents(OUTPUT_FILE, $match[1]."\n", FILE_APPEND);
		}
	}
	$start++;
}

function get($url)
{
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.94 Safari/537.36');
	$ret = curl_exec($ch);
	unset($ch);
	sleep(SLEEP_TIME);
	return $ret;
}

function parse($content)
{
	$content = str_replace(array("\r", "\n"), '', $content);
	preg_match_all('/\'F\'\:.*?href.*?"(.*?)"/i', $content, $match);
	if (is_array($match[1]))
		return $match[1];
	return false;
}
