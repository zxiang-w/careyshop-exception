<?php
/**
 * @copyright   Copyright (c) http://careyshop.cn All rights reserved.
 *
 * CareyShop    API结果输出
 *
 * @author      zxm <252404501@qq.com>
 * @date        2020/7/20
 */

namespace careyshop;

use think\facade\Config;
use think\facade\Request;
use think\Response;

class ApiOutput
{
    /**
     * 输出格式
     * @var string
     */
    public static $format = 'json';

    /**
     * 默认响应头
     * @var array
     */
    public static $header = [];
    public static $headerKey;
    public static $headerValue;

    /**
     * @param $result
     * @param $code
     * @return \think\response\Json
     */
    public static function outJson($result, $code)
    {
        return json($result, $code, self::$header);
    }

    /**
     * @param $result
     * @param $code
     * @return \think\response\Xml
     */
    public static function outXml($result, $code)
    {
        return xml($result, $code, self::$header, ['root_node' => 'careyshop']);
    }

    /**
     * @param $result
     * @param $code
     * @return \think\response\Jsonp
     */
    public static function outJsonp($result, $code)
    {
        return jsonp($result, $code, self::$header);
    }

    /**
     * @param $result
     * @param $code
     * @return \think\response\View
     */
    public static function outView($result, $code)
    {
        header(self::$headerKey . ': ' . self::$header[self::$headerKey]);
        return view('common@/CareyShop', ['data' => $result], $code);
    }

    /**
     * @param $result
     * @param $code
     * @return Response
     */
    public static function outResponse($result, $code)
    {
        if ($result instanceof Response) {
            $header = array_merge($result->getHeader(), self::$header);
            return $result->code($code)->header($header);
        }

        return $result;
    }

    /**
     * 数据输出
     * @access public
     * @param array  $data    数据
     * @param int    $code    状态码
     * @param bool   $error   正常或错误
     * @param string $message 提示内容
     * @return mixed
     */
    public static function outPut($data = [], $code = 200, $error = false, $message = '')
    {
        // 区分返回数据类型
        if (isset($data['callback_return_type']) && array_key_exists('is_callback', $data)) {
            // 自定义回调接口返回
            self::$format = $data['callback_return_type'];
            $result = $data['is_callback'];
        } else {
            // 返回结构确定
            $result = [
                'status'  => $code,
                'message' => $error == true ? empty($message) ? '发生未知异常' : $message : 'success',
                'data' => Config::get('app.empty_result'),
            ];

            if (!$error) {
                $result['data'] = !empty($data) ? $data : Config::get('app.empty_result');
            } else {
                // 状态(非HTTPS始终为200状态,防止运营商劫持)
                $code = Request::isSsl() ? $code : 200;
            }
        }

        // 按请求格式返回
        self::$headerKey = base64_decode('WC1Qb3dlcmVkLUJ5');
        self::$headerValue = base64_decode('Q2FyZXlTaG9w');
        self::$header[self::$headerKey] = self::$headerValue . '/' . get_version();

        switch (self::$format) {
            case 'view':
                return self::outView($result, $code);

            case 'response':
                return self::outResponse($result, $code);

            case 'jsonp':
                return self::outJsonp($result, $code);

            case 'xml':
                return self::outXml($result, $code);

            case 'json':
            default:
                return self::outJson($result, $code);
        }
    }
}
