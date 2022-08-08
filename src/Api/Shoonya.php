<?php

namespace Core\Api;

use GuzzleHttp\Client;
use Exception;

class Shoonya {

    protected $guzzle, $jKey, $userName, $accountId, $pwd, $uid,$exarr,$brkname,$email;
    protected $cred,$orderNo = [];
    protected const Delivery = 'C', Intraday = 'I', Normal = 'M', CF = 'M';
    protected const FeedTouchLine = 1,FeedSnapQuoate=2;
    protected const PriceMarket = 'MKT', PriceLimit = 'LMT', PrinceSLLmit = 'SL-LMT', PriceSLM = 'SL-MKT';
    Protected const Buy = 'B', Sell = 'S';
    
    protected $urls = [
        'host' => 'https://shoonyatrade.finvasia.com/',
        'websocket_endpoint' => 'wss://shoonyatrade.finvasia.com/NorenWSTP',
        'endpoint' => 'https://shoonyatrade.finvasia.com/NorenWClientTP',
        "eodhost" => 'https://shoonya.finvasia.com/chartApi/getdata',
    ];
    protected $routes = [
        'login' => '/QuickAuth',
        'logout' => '/Logout',
        'forgot_password' => '/ForgotPassword',
        'change_password' => '/Changepwd',
        'watchlist_names' => '/MWList',
        'watchlist' => '/MarketWatch',
        'watchlist_add' => '/AddMultiScripsToMW',
        'watchlist_delete' => '/DeleteMultiMWScrips',
        'placeorder' => '/PlaceOrder',
        'modifyorder' => '/ModifyOrder',
        'cancelorder' => '/CancelOrder',
        'exitorder' => '/ExitSNOOrder',
        'product_conversion' => '/ProductConversion',
        'orderbook' => '/OrderBook',
        'tradebook' => '/TradeBook',
        'singleorderhistory' => '/SingleOrdHist',
        'searchscrip' => '/SearchScrip',
        'TPSeries' => '/TPSeries',
        'optionchain' => '/GetOptionChain',
        'holdings' => '/Holdings',
        'limits' => '/Limits',
        'positions' => '/PositionBook',
        'scripinfo' => '/GetSecurityInfo',
        'getquotes' => '/GetQuotes',
    ];

    public function __construct() {
        $this->cred = parse_ini_file('cred.ini');
        $this->guzzle = new Client();
    }
    
    /**
     * 
     * @return bool
     * @throws Exception
     */
    public function login():bool {
        $this->cred['pwd'] = hash('sha256', utf8_encode($this->cred['pwd']));
        $this->cred['appkey'] = hash('sha256', utf8_encode($this->cred['uid'] . '|' . $this->cred['appkey']));
        $request = $this->post($this->routes['login'], $this->jData($this->cred),false);
        $decode = $this->decode($request->getBody());
        if ($decode->stat != 'Ok') {
            throw new Exception($decode->emsg .  'LoginError', $request->getStatusCode());
        }
        $this->sessionData($decode);
        return true;
    }

    /**
     * 
     * @return bool
     * @throws Exception
     */
    public function logout() :bool {
        $request = $this->post($this->routes['logout'], $this->jData(['ordersource' => 'API', 'uid' => $this->uid]));
        $decode = $this->decode($request->getBody());
        if ($decode->stat != 'Ok') {
            throw new Exception($decode->emsg .  'LogoutError', $request->getStatusCode());
        }
        $this->jKey = null;
        $this->userName = null;
        $this->accountId = null;
        $this->uid = null;
        return true;
    }
    
    /**
     * 
     * @param string $exchange
     * @param string $searchtext
     * @return array
     * @throws Exception
     */
    public function searchScrip(string $exchange, string $searchtext):array {
        if ($searchtext == null) {
            throw new Exception('search text cannot be null');
        }

        $values = [
            'uid' => $this->uid,
            'exch' => $exchange,
            'stext' => $searchtext // urllib . parse . quote_plus
        ];

        $request = $this->post($this->routes['searchscrip'], $this->jData($values));
        $decode = $this->decode($request->getBody());
        if ($decode->stat != 'Ok') {
            throw new Exception($decode->emsg . 'ScripSearch-Error' , $request->getStatusCode());
        }
        return $decode->values;
    }
    
    /**
     * 
     * @param string $prd
     * @param string $seg
     * @param string $exch
     * @return type
     */
    public function getLimits($prd=null,$seg=null,$exch=null) {
        $values = [
            'uid' => $this->uid,
            'actid' => $this->accountId
        ];
        
        if(!is_null($prd)){
            $values['prd'] = $prd;
        }
        if(!is_null($seg)) {
            $values['seg'] = $seg;
        }
        if(!is_null($exch)){
            $values['exch'] = $exch;
        }
        $request = $this->post($this->routes['limits'], $this->jData($values));
        $decode = $this->decode($request->getBody());
        return $decode;
    }
    
    /**
     * 
     * @param string $token
     * @param string $exchange
     * @return array
     * @throws Exception
     */
    public function getQuotes(string $token, string $exchange ='BSE' ){
        if ($token == null) {
            throw new Exception('token text cannot be null');
        }
        $tkNum = parse_ini_file('scrip/bse.ini');
        $values = [
            'uid'=> $this->accountId,
            'exch'=>$exchange,
            'token'=>$tkNum[$token]
        ];
        
        $request = $this->post($this->routes['getquotes'], $this->jData($values));
        $decode = $this->decode($request->getBody());
        if($decode->stat != 'Ok') {
            throw new Exception($decode->emsg . 'getQuotes-Error', $request->getStatusCode());
        }
        return $decode;
    }
    
    /**
     * 
     * @param string $token
     * @param string $startTime d-m-Y
     * @param string $endTime
     * @param string $interval 1, 3, 5 , 10, 15, 30, 60, 120, 240
     * @param string $exch
     */
    public function getTimePriceSeries(string $token,string $startTime = null, string $endTime=null, string $interval='240', string $exch='BSE') {
        $tkNum = parse_ini_file('scrip/bse.ini');
        if(is_null($startTime)) {
            $startTime = (string)strtotime(date('d-m-Y'));
        }
        else{
            $startTime = (string)strtotime($startTime);
        }
        
        $values = [
            'uid'=> $this->accountId,
            'exch'=>$exch,
            'token'=>$tkNum[$token],
            'st'=> $startTime
        ];
        if(!is_null($endTime)) {
            $values['et'] = (string)strtotime($endTime);
        }
        if(!is_null($interval)) {
            $values['intrv'] = (string) $interval;
        }
        $request = $this->post($this->routes['TPSeries'], $this->jData($values));
        $decode = $this->decode($request->getBody());
        return $decode;
    }


    /**
     * 
     * @return array
     */
    public function getOrderbook():array {
        $values = ['uid'=> $this->uid];
        $request = $this->post($this->routes['orderbook'], $this->jData($values));
        $decode = $this->decode($request->getBody());
        return $decode;
    }
    public function getTradebook() {
        $values = ['uid'=> $this->uid,'actid'=> $this->accountId];
        $request = $this->post($this->routes['tradebook'], $this->jData($values));
        $decode = $this->decode($request->getBody());
        return $decode;
    }

    /**
     * 
     * @param string $productType
     * @return array | stdClass
     * @throws Exception
     */
    public function getHoldings($productType = self::Delivery) {

        $values = [
            'uid' => $this->uid,
            'actid' => $this->accountId,
            'prd' => $productType
        ];
        $request = $this->post($this->routes['holdings'], $this->jData($values));
        $decode = $this->decode($request->getBody());
        return $decode;
    }
    
    /**
     * 
     * @return array | stdClass
     * @throws Exception
     */
    public function getPositions() {
        $values = [
            'uid' => $this->uid,
            'actid' => $this->accountId
        ];
        $request = $this->post($this->routes['positions'], $this->jData($values));
        $decode = $this->decode($request->getBody());
        return $decode;
    }

    public function placeOrder($buy_or_sell, $productType, $exchange, $tradingSymbol, $quantity, $discloseQty,
            $priceType, $price = 0.0, $triggerPrice = null, $retention = 'DAY', $amo = 'NO', $remarks = null,
            $booklossPrice = 0.0, $bookprofitPrice = 0.0, $trailPrice = 0.0) {

        #prepare the data
        $values = ['ordersource' => 'API',
            'uid' => $this->uid,
            'actid' => $this->accountId,
            'trantype' => $buy_or_sell,
            'prd' => $productType,
            'exch' => $exchange,
            'tsym' => ($tradingSymbol), //urllib . parse . quote_plus
            'qty' => string($quantity),
            'dscqty' => string($discloseQty),
            'prctyp' => $priceType,
            'prc' => string($price),
            'trgprc' => string($triggerPrice),
            'ret' => $retention,
            'remarks' => $remarks,
            'amo' => $amo
        ];

        #if cover order or high leverage order
        if ($productType == 'H') {
            $values["blprc"] = string($booklossPrice);
            #trailing price
            if ($trailPrice != 0.0) {
                $values["trailprc"] = string($trailPrice);
            }
        }

        #bracket order
        if ($productType == 'B') {
            $values["blprc"] = string($booklossPrice);
            $values["bpprc"] = string($bookprofitPrice);
            #trailing price
            if ($trailPrice != 0.0) {
                $values["trailprc"] = string($trailPrice);
            }
        }

        $request = $this->post($this->routes['placeorder'], $this->jData($values));
        $decode = $this->decode($request->getBody());
        if ($decode->stat != 'Ok') {
            throw new Exception($decode->emsg.'OrderPlacing-Error', $request->getStatusCode());
        }
        $this->orderNo[] = $decode->norenordno;
        return true;
    }
    
    public function getSessionData() {
        return ['jKey' => $this->jKey,'uid'=>$this->uid,'actid'=> $this->accountId, 'uname'=>$this->userName, $this->exarr, $this->brkname];
    }
    
    protected function sessionData($data) {
        $this->jKey = $data->susertoken;
        $this->userName = $data->uname;
        $this->accountId = $data->actid;
        $this->uid = $data->actid;
        $this->exarr = $data->exarr;
        $this->brkname = $data->brkname;
        $this->email = $data->email;
    }

    protected function decode($jsonData) {
        return json_decode($jsonData);
    }

    protected function post($routes, $body, $contentType = 'application/json') {
        $url = $this->urls['endpoint'] . $routes;
        return $this->guzzle->post($url, [
                    'header' => ['Content-Type' => $contentType],
                    'body' => $body
        ]);
    }

    protected function jData($data,$isKey = true) {
        if($isKey){
            return 'jData=' .json_encode($data) . $this->jKey();
        }
        return 'jData=' . json_encode($data);
    }

    protected function jKey() {
        return '&jKey=' . $this->jKey;
    }

}
