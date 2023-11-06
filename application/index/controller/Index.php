<?php

namespace app\index\controller;

use app\index\code\IntConvert;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Hashids\Hashids;
use think\facade\Log;
use think\facade\Request;

class Index
{

    public function js()
    {
        return view('js');
    }

    public function index()
    {

        $da = Request::param();
        $url = 'https://www.iwencai.com/customized/chart/get-robot-data';
        $header = [
            'Content-Type' => 'application/json',
            'User-Agent'   => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36',
        ];
        $header['cookie'] = 'v=' . $da['hexin'] ?? 'A3qgdH3Uy8LChkfcV9Uo5JMMzauZK_9OcKtyqYRxJxzYVxQVbLtOFUA_wpxX';
        $header['Hexin-V'] = $da['hexin'] ?? 'A3qgdH3Uy8LChkfcV9Uo5JMMzauZK_9OcKtyqYRxJxzYVxQVbLtOFUA_wpxX';

        $day = $da['day'] ?? '2023-11-03';
//        $day1 = '20231102';
//
//        $data = [
//            "source"   => "Ths_iwencai_Xuangu",
//            "version"  => "2.0",
//            "question" => $day . "日竞价涨幅;{$day1}日2板以上;非ST;",
//            'page'     => 1,
//            'perpage'  => 120,
//        ];
//
//        $res = $this->http_post($url, $data, 'POST', 'json', $header);
//        $res = json_decode($res, true);
//        dd($res['data']['answer'][0]['text_answer'] ?? '', $res['data']['answer'][0]['txt'][0]['content']['components'][0]['data']['meta']['extra']['row_count'] ?? 0);


        $items = $this->getWhere($day);
//        dd($da, $items);


        $items_data = [];
        foreach ($items as $item) {
            $res = $this->http_post($url, $item['where'], 'POST', 'json', $header);
            $res = json_decode($res, true);
            $res2 = $this->http_post($url, $item['where_2'], 'POST', 'json', $header);
            $res2 = json_decode($res2, true);

            $res_c = $res['data']['answer'][0]['txt'][0]['content']['components'][0]['data']['meta']['extra']['row_count'] ?? 0;
            $res2_c = $res2['data']['answer'][0]['txt'][0]['content']['components'][0]['data']['meta']['extra']['row_count'] ?? 0;

            $items_data[] = [
                'type'           => $item['type'] ?? '',
                'where'          => $item['where']['question'] ?? '',
                '总数'           => $res_c,
                '总数竞价涨幅>0' => $res2_c,
                '溢价率'         => $res_c == 0 ? 0 : $res2_c / $res_c,
            ];
        }

        return json($items_data);
        dd($items_data);


//        echo $this->createNonceStr(6);

//        $hashids = new Hashids('', 6); // pad to length 10
//
//        echo $hashids->encode(18206766729);
//        echo PHP_EOL;
//        echo $this->inviteCode6(18206766729);
//        echo PHP_EOL;
//        echo substr(base_convert(md5(uniqid(md5(microtime(true)), true)), 16, 10), 0, 6);
//
        return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:) </h1><p> ThinkPHP V5.1<br/><span style="font-size:30px">16载初心不改（2006-2022） - 你值得信赖的PHP框架</span></p></div><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="eab4b9f840753f8e7"></think>';
    }


    /**
     * @param $day
     * @param $txt
     *
     * @author: kong | <iwhero@yeah.com>
     * @date  : 2023/11/4 13:42
     */
    public function getWhere($day)
    {
        $day = date('Y-m-d', strtotime($day));
        $day1 = date('Y-m-d', strtotime($day) - (23 * 60 * 60));

        $tx_data = ['2板以上', '炸板', '涨停', '首板'];

        $data = [];
        foreach ($tx_data as $txt) {
            $data[] = [
                'type'    => $txt,
                'where'   => [
                    "source"   => "Ths_iwencai_Xuangu",
                    "version"  => "2.0",
                    "question" => $day . "日竞价涨幅;{$day1}日{$txt};非ST非8开头;",
                    'page'     => 1,
                    'perpage'  => 120,
                ],
                'where_2' => [
                    "source"   => "Ths_iwencai_Xuangu",
                    "version"  => "2.0",
                    "question" => $day . "日竞价涨幅>0;{$day1}日{$txt};非ST非8开头;",
                    'page'     => 1,
                    'perpage'  => 120,
                ],
            ];
        }


        return $data;

    }


    public function http_post($url, $data = "", $method = 'POST', $type = "form", $header = [])
    {
        try {
            $client = $this->cleint(['http_errors' => false, 'connect_timeout' => 30, 'timeout' => 30]);

            $send_parms = [];
            $send_parms['headers'] = $header;

            if ($type == 'form') {
                $send_parms['form_params'] = $data;
            } elseif ($type == 'json') {
                $send_parms['json'] = $data;
            } elseif ($type == 'query') {
                $send_parms['query'] = $data;
            } else {

                return false;
            }

            $response = $client->request($method, $url, $send_parms);

            $code = $response->getStatusCode();
            if (empty($code)) {

                return false;
            }

            $content = $response->getBody()->getContents();

            if (empty($content)) {

                return false;
            }

        } catch (RequestException $e) {

            Log::write($e->getMessage());

            return false;
        }

        return $content;
    }


    protected function cleint($config = [])
    {
        return app(\GuzzleHttp\Client::class, $config);
    }


    public function hello($name = 'ThinkPHP5')
    {
        echo IntConvert::toString(1213678);
    }

    public function inviteCode6($id)
    {
        //10进制转2进制翻转，补位避免数字翻转塌陷。
        $id = base_convert('1' . substr(strrev((base_convert($id + 3221225472, 10, 2))), 0, -1), 2, 10);
        //字典字母顺序可打乱
        $dict = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $base = strlen($dict);
        $code = '';
        //自定义进制转换
        do {
            $code = $dict[ bcmod($id, $base) ] . $code;
            $id = bcdiv($id, $base);
        } while ($id > 0);

        return $code;
    }

    public function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        return $str;
    }

    public function initcode($length = 6)
    {
        $code = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $rand = $code[ rand(0, 25) ]
            . strtoupper(dechex(date('m')))
            . date('d') . substr(time(), -5)
            . substr(microtime(), 2, 5)
            . sprintf('%02d', rand(0, 99));

        for (
            $a = md5($rand, true),
            $s = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
            $d = '',
            $f = 0;
            $f < $length;
            $g = ord($a[ $f ]),
            $d .= $s[ ($g ^ ord($a[ $f + 8 ])) - $g & 0x1F ],
            $f++
        ) ;

        return $d;
    }
}
