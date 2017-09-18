<?php

namespace Codeages\Biz\Framework\Pay\Payment;


use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class LianlianPayGetway extends AbstractGetway
{
    protected $url = 'https://yintong.com.cn/payment/bankgateway.htm';

    protected $isWap = false;

    public function createTrade($data)
    {
        if (!ArrayToolkit::requireds($data, array(
            'goods_title',
            'goods_detail',
            'attach',
            'trade_sn',
            'amount',
            'notify_url',
            'return_url',
            'create_ip',
        ))) {
            throw new InvalidArgumentException('trade args is invalid.');
        }

        $platformType = empty($data['platform_type']) ? 'Web' : $data['platform_type'];

        if ($platformType == 'Wap') {
            $this->url = 'https://yintong.com.cn/llpayh5/payment.htm';
            $this->isWap = true;
        }

        return $this->convertParams($data);
    }



    public function converterNotify($data)
    {
        $data = ArrayToolkit::parts($data, array(
            'oid_partner',
            'sign_type',
            'sign',
            'dt_order',
            'no_order',
            'oid_paybill',
            'money_order',
            'result_pay',
            'settle_date',
            'info_order',
            'pay_type',
            'bank_code'
        ));

        $postSign = $data['sign'];
        unset($data['sign']);

        $sign = $this->signParams($data);

        if ($postSign != $sign) {
            return array(
                array(
                    'status' => 'failture',
                    'notify_data' => $data,
                ),
                'fail'
            );
        }

        return array(array(
                'status' => 'paid',
                'cash_flow' => $data['oid_paybill'],
                'paid_time' => $data['settle_date'],
                'pay_amount' => (int)($data['money_order']*100),
                'cash_type' => 'CNY',
                'trade_sn' => $data['no_order'],
                'attach' => array(),
                'notify_data' => $data,
            ),
            'success'
        );
    }

    public function converterRefundNotify($data)
    {
        // TODO: Implement converterRefundNotify() method.
    }

    public function queryTrade($trade)
    {
        // TODO: Implement queryTrade() method.
    }

    public function applyRefund($data)
    {
        // TODO: Implement applyRefund() method.
    }

    protected function signParams($params)
    {
        ksort($params);
        $sign = '';
        foreach ($params as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $sign .= $key.'='.$value.'&';
        }

        $setting = $this->getSetting();
        $sign .= 'key='.$setting['secret'];
        return md5($sign);
    }

    protected function convertParams($params)
    {
        $setting = $this->getSetting();
        $converted                 = array();
        $converted['busi_partner'] = '101001';
        $converted['dt_order']     = date('YmdHis', time());
        $converted['money_order']  = $params['amount'];
        $converted['name_goods']   = mb_substr($this->filterText($params['goods_title']), 0, 12, 'utf-8');
        $converted['no_order']     = $params['trade_sn'];
        if (!empty($params['notify_url'])) {
            $converted['notify_url'] = $params['notify_url'];
        }
        $converted['sign_type']    = 'MD5';
        $converted['version']      = '1.0';

        $converted['oid_partner']  = $setting['oid_partner'];
        $identify = $this->getDefaultIdentify($params);
        $converted['user_id']      = $identify."_".$params['attach']['user_id'];

        $converted['timestamp']    = date('YmdHis', time());
        if (!empty($params['return_url'])) {
            $converted['url_return'] = $params['return_url'];
        }
        $converted['risk_item']  = json_encode(array(
            'frms_ware_category'=>1008,
            'user_info_mercht_userno'=>$identify."_".$params['attach']['user_id'],
            'user_info_dt_register'=>date('YmdHis', $params['attach']['user_created_time'])
        ));

        $converted['userreq_ip'] = str_replace(".", "_", $params['create_ip']);
        $converted['bank_code']  = '';
        $converted['pay_type']   = '2';
        if ($this->isWap) {
            $converted['back_url'] = $params['back_url'];
        }
        $converted['sign']       = $this->signParams($converted);

        if ($this->isWap) {
            return $this->convertMobileParams($converted);
        } else {
            return $converted;
        }
    }

    protected function convertMobileParams($converted)
    {
        unset($converted['userreq_ip'], $converted['bank_code'], $converted['pay_type'], $converted['timestamp'], $converted['version'], $converted['sign']);
        $converted['version'] = '1.2';
        $converted['app_request'] = 3;
        $converted['sign'] = $this->signParams($converted);
        return array('req_data'=>json_encode($converted));
    }

    protected function filterText($text)
    {
        preg_match_all('/[\x{4e00}-\x{9fa5}A-Za-z0-9.]*/iu', $text, $results);
        $title = '';
        if ($results) {
            foreach ($results[0] as $result) {
                if (!empty($result)) {
                    $title .= $result;
                }
            }
        }

        return $title;
    }

    protected function getDefaultIdentify($params)
    {
        if (empty($params['llpay_identify'])) {
            return substr(md5(uniqid()), 0, 12);
        }
        return $params['llpay_identify'];
    }

    protected function getSetting()
    {
        $config = $this->biz['payment.platforms']['lianlianpay'];
        return array(
            'secret' => $config['secret'],
            'oid_partner' => $config['oid_partner'],
        );
    }
}