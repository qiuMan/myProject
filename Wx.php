<?php
/**
 * 微信SDK
 * pan041ymail@gmail.com
 */
class Wx
{
    private  $appid = 'wxbdc5610cc59c1631';
    private  $appsecret = 'c1a56a5c4247dd44c320c9719c5ceb90';
    private  $scope = 'snsapi_login';
    private  $redirect_uri = 'https://passport.yhd.com/wechat/Fcallback.do';
    //构造函数，获取Access Token
    public function __construct($appid = NULL, $appsecret = NULL)
    {

    }

    public function login()
    {
        $state = md5(rand(100000, 999999));
        $_SESSION['state'] = $state;
        $url = $this->qrconnect($this->redirect_uri, $this->scope, $state);
        header('Location:' . $url);
    }

    /**
     * 回调函数获取登录用户信息
     * @return array|mixed
     */
    public function getUserInfo()
    {
        $code = $_GET['code'];
        $state = $_GET['state'];

        $user=[];
        if ($state == $_SESSION['state']) {
            $access_token = $this->oauth2_access_token($code);
            if (!isset($access_token['errcode'])) {
                $user= $this->oauth2_get_user_info($access_token['access_token'],$access_token['openid']);
            }
        }

        return $user;

    }
    /**
     * 获取code
     * @param $redirect_url
     * @param $scope
     * @param null $state
     * @return string
     */
    private function qrconnect($redirect_url, $scope, $state = NULL)
    {
        $url = "https://open.weixin.qq.com/connect/qrconnect?appid=" . $this->appid . "&redirect_uri=" . urlencode($redirect_url) . "&response_type=code&scope=" . $scope . "&state=" . $state . "#wechat_redirect";
        return $url;
    }

    /**
     * 获取access_token
     * @param $code
     * @return mixed
     */
    private function oauth2_access_token($code)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $this->appid . "&secret=" . $this->appsecret . "&code=" . $code . "&grant_type=authorization_code";
        $res = $this->http_request($url);
        return json_decode($res, true);
    }

    /**
     * 通过refresh_token获取access_token
     * @param $code
     * @return mixed
     */
    private function access_token_by_refresh_token($refresh_token)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=".$this->appid."&grant_type=refresh_token&refresh_token={$refresh_token}";
        $res = $this->http_request($url);
        return json_decode($res, true);
    }

    /**
     * 获取用户基本信息（OAuth2 授权的 Access Token 获取 未关注用户，Access Token为临时获取）
     * @param $access_token
     * @param $openid
     * @return mixed
     */
    private function oauth2_get_user_info($access_token, $openid)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $access_token . "&openid=" . $openid . "&lang=zh_CN";
        $res = $this->http_request($url);
        return json_decode($res, true);
    }

    /**
     * https请求
     * @param $url
     * @param null $data
     * @return mixed
     */
    private function http_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}