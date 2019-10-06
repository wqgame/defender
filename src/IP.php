<?php

namespace Defender;

require_once __DIR__ . '/../vendor/autoload.php';

use itbdw\Ip\IpLocation;

/**
 * IP相关防护库
 */
class IP {
    
    /**
     * IP是否被传入的列表阻止
     * @param  array   $ip_list   阻止指定的ip列表，如: ['127.0.0.1', '163.177.65.160/24']
     * @param  string  $client_ip 客户端ip
     * @return boolean            是否阻止
     */
    public static function isBlockedByList($ip_list, $client_ip = '') {
        if (empty($ip_list)) {
            return false;
        }
        $client_ip = $client_ip ?: self::getClientIp();
        if (is_numeric($client_ip)) {
            $client_ip = long2ip($client_ip);
        }
        $ip_sets = [];
        foreach ($ip_list as $value) {
            if (stripos($value, '/') !== false) {
                // 扩展成多ip
            }
            if (is_numeric($value)) {
                $value = long2ip($value);
            }
            $ip_sets[$value] = true;
        }
        if (isset($ip_sets[$client_ip])) {
            return true;
        }
        return false;
    }

    /**
     * 是否被传入的地区名列表阻止
     * @param  array   $district_list 地区名列表，如: ['美国', '香港', '网吧', '联通']
     * @param  string  $client_ip     客户端ip
     * @return boolean                是否阻止
     */
    public static function isBlockedByDistrictList($district_list = [], $client_ip = '') {
        if (empty($district_list)) {
            return false;
        }
        $client_ip = $client_ip ?: self::getClientIp();
        if (is_numeric($client_ip)) {
            $client_ip = long2ip($client_ip);
        }
        $location = IpLocation::getLocation($client_ip);
        $district = $location['country'] . $location['province'] . $location['city'] . $location['county'] . $location['isp'] . $location['area'];
        foreach ($district_list as $name) {
            if (stripos($district, $name) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取客户端ip
     */
    public static function getClientIp() {
        $ips = array();
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ips[] = $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ips[] = $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_PROXY_USER'])) {
            $ips[] = $_SERVER['HTTP_PROXY_USER'];
        }
        $real_ip = getenv('HTTP_X_REAL_IP');
        if (!empty($real_ip)) {
            $ips[] = $real_ip;
        }
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $ips[] = $_SERVER['REMOTE_ADDR'];
        }
        // 选第一个最合法的，或最后一个正常的IP
        foreach ($ips as $ip) {
            $long = ip2long($ip);
            $long && $final = $ip;
            // 排除不正确的或私有IP
            if (!(($long == 0) ||                                       // 不正确的IP或0.0.0.0
                  ($long == -1) ||                                      // PHP4下当IP不正确时会返回-1
                  ($long == 0xFFFFFFFF) ||                              // 255.255.255.255
                  ($long == 0x7F000001) ||                                // 127.0.0.1
                  (($long >= 0x0A000000) && ($long <= 0x0AFFFFFF)) ||   // 10.0.0.0 - 10.255.255.255
                  (($long >= 0xC0A8FFFF) && ($long <= 0xC0A80000)) ||   // 172.16.0.0 - 172.31.255.255
                  (($long >= 0xAC1FFFFF) && ($long <= 0xAC100000)))) {  // 192.168.0.0 - 192.168.255.255
                $final = long2ip($long);
                break;
            }
        }
        empty($final) && $final = '0.0.0.0';
        return $final;
    }
}