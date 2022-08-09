<?php

namespace Core\Api;

use GuzzleHttp\Client;
use Katzgrau\KLogger\Logger;

class Shoonya {

    protected $guzzle, $jKey, $userName, $accountId, $pwd, $uid,$exarr,$brkname,$email;
    protected $cred,$logger,$orderNo = [];
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
        $this->logger = new Logger('log/');
    }
    
    /**
     * 
     * @return bool
     */
    public function login():bool {
        $this->cred['pwd'] = hash('sha256', utf8_encode($this->cred['pwd']));
        $this->cred['appkey'] = hash('sha256', utf8_encode($this->cred['uid'] . '|' . $this->cred['appkey']));
        $req = $this->request('login', $this->cred, false);
        if($this->log($req, ['logged in successfully!','falied to loggedin!'])){
            $this->sessionData($req);
            return true;
        }
        return false;
    }

    /**
     * 
     * @return bool
     */
    public function logout() :bool {
        $req = $this->request('logout', ['ordersource' => 'API', 'uid' => $this->uid]);
        if($this->log($req, ['logout successfully!','failed to logout!'])){
            $this->jKey = null;
            $this->userName = null;
            $this->accountId = null;
            $this->uid = null;
            return true;
        }
        return false;
    }
    
    /**
     * 
     * @param string $uid
     * @param string $pan
     * @param string $dob
     * @return bool
     */
    public function forgotPassword(string $uid, string $pan, string $dob):bool {
        $values = [
            'source'=>'API',
            'uid' => $uid,
            'pan' => $pan,
            'dob' => $dob
        ];
        $req = $this->request('forgot_password', $values,false);
        return $this->log($req, ['Password Changed Successfully','Could not changed password!']);   
    }
    
    /**
     * 
     * @param string $searchtext
     * @param string $exchange
     * @return array
     */
    public function searchScrip(string $searchtext, string $exchange ='BSE'):array {
        $values = [
            'uid' => $this->uid,
            'exch' => $exchange,
            'stext' => $searchtext // urllib . parse . quote_plus
        ];
        return $this->request('searchscrip', $values)->values;
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
        return $this->request('limits', $values);
    }
    
    public function getOptionChain(string $tsym, int $strprc,int $count=5, string $exch='NFO') {
        $values = [
            'uid'=> $this->uid,
            'exch'=>$exch,
            'tsym' =>$tsym,
            'strprc' => "$strprc",
            'cnt'=> "$count"
        ];
        return $this->request('optionchain', $values);
    }
    
    /**
     * 
     * @param string $token
     * @param string $exch
     */
    public function getScripInfo(string $token,string $exch='BSE') {
        $tkNum = parse_ini_file('scrip/bse.ini');
        $values = [
            'uid'=> $this->uid,
            'exch'=>$exch,
            'token'=>$tkNum[$token]
        ];
        return $this->request('scripinfo', $values);
    }
    
    /**
     * 
     * @param string $token
     * @param string $exchange
     * @return array
     */
    public function getQuotes(string $token, string $exchange ='BSE' ){
        $tkNum = parse_ini_file('scrip/bse.ini');
        $values = [
            'uid'=> $this->accountId,
            'exch'=>$exchange,
            'token'=>$tkNum[$token]
        ];
        return $this->request('getquotes', $values);
    }
    
    /**
     * 
     * @param string $token
     * @param string $startTime d-m-Y
     * @param string $endTime
     * @param string $interval 1, 3, 5 , 10, 15, 30, 60, 120, 240
     * @param string $exch
     */
    public function getTimePriceSeries(string $token,string $startTime = null, string $endTime=null, string $interval='15', string $exch='BSE') {
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
        return $this->request('TPSeries', $values);
    }
    
    /**
     * 
     * @param string $token
     * @param string $startDate
     * @param string $endDate
     * @param string $exch
     * @return type
     */
    public function getDailyPriceSeries(string $token,string $startDate, string $endDate=null, string $exch='BSE') {
        $tkNum = parse_ini_file('scrip/bse.ini');
        if(is_null($endDate)) {
            $et = (string) strtotime(date('d-m-Y'));
        }else {
            $et=  (string) strtotime($endDate);
        }
        $st = (string) strtotime($startDate);
        
        $values = [
            'uid'=> $this->accountId,
            'sym'=>" $exch : $tkNum[$token]" ,
            'from'=>$st,
            'to'=> $et
        ];
        $request = $this->guzzle->post($this->urls['eodhost'], [
                    'header' => ['Content-Type' =>  'application/json'],
                    'body' => $this->jData($values)
        ]);
        $decode = $this->decode($request->getBody());
        return $decode;
    }
    
    public function positionProductConversion() {
        
    }
    
    /**
     * 
     * @param int $orderNo
     * @return type
     */
    public function singleOrderHistory(int $orderNo) {
        return $this->request('singleorderhistory', ['uid'=> $this->uid,'norenordno'=>$orderNo]);
    }

    /**
     * 
     * @return array
     */
    public function getOrderbook() {
        return $this->request('orderbook', ['uid' => $this->uid]);
    }
    
    /**
     * 
     * @return type
     */
    public function getTradebook() {
        return $this->request('tradebook', ['uid' => $this->uid,'actid'=> $this->accountId]);
    }

    /**
     * 
     * @param string $productType
     * @return array | stdClass
     */
    public function getHoldings($productType = self::Delivery) {
        return $this->request('holdings', ['uid' => $this->uid,'actid' => $this->accountId,'prd' => $productType]);
    }
    
    /**
     * 
     * @return array | stdClass
     */
    public function getPositions() {
        return $this->request('positions',['uid' => $this->uid, 'actid' => $this->accountId]);
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
        $req = $this->request('placeorder', $values);
        if($this->log($req, ["order placed, ON:- $req->norenordno", 'failed to place order!'])) {
            $this->orderNo[] = $req->norenordno;
            return true;
        }
        return false;
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
    
    /**
     * 
     * @param object $req
     * @param array $msg
     * @return boolean
     */
    protected function log($req,array $msg) {
        if($req->stat == 'Ok') {
            $this->logger->info("User {$this->cred['uid']} " . $msg[0]);
            return true;
        }
        $this->logger->notice("User {$this->cred['uid']} " . $msg[1]);
        return false ;
    }


    protected function request(string $routes, array $jData,$iskey=true){
        $request = $this->post($this->routes[$routes], $this->jData($jData),$iskey);
        $decode = $this->decode($request->getBody());
        return $decode;
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
