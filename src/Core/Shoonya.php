<?php

namespace Core;

use GuzzleHttp\Client;
use Katzgrau\KLogger\Logger;

/**
 * @category API
 * @package Shoonya-php
 * @version 1.0.1
 * @license MIT
 * @copyright (c) 2022, Shubham Chaudhary
 * @author Shubham Chaudhary <ghost.jat@gmail.com>
 */
class Shoonya {

    protected $wsC, $guzzle, $jKey, $userName, $accountId, $pwd, $uid, $exarr, $brkname, $email;
    protected $cred, $logger, $orderBook = [];

    public const Delivery = 'C', Intraday = 'I', Normal = 'M', CF = 'M';
    public const FeedTouchLine = 't', FeedSnapQuoate = 'd';
    public const PriceMarket = 'MKT', PriceLimit = 'LMT', PrinceSLLmit = 'SL-LMT', PriceSLM = 'SL-MKT', DS = 'DS', L2 = '2L', L3 = '3L';
    public const Buy = 'B', Sell = 'S', AITG = 'LTP_A_O', AITL = 'LTP_B_O';

    public $tmp;
    protected $urls = [
        'host' => 'https://api.shoonya.com/',
        'ws_endpoint' => 'wss://api.shoonya.com/NorenWSTP',
        'endpoint' => 'https://api.shoonya.com/NorenWClientTP',
        "eodhost" => 'https://api.shoonya.com/chartApi/getdata',
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
        'placegttorder' => '/PlaceGTTOrder',
        'cancelgttorder' => '/CancelGTTOrder',
        'getpendinggtt' => '/GetPendingGTTOrder',
        'getenabledgtts' => '/GetEnabledGTTs',
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
        'setalert' => '/SetAlert',
        'cancelalert' => '/CancelAlert',
        'modifyalert' => '/ModifyAlert',
        'getpendingalert' => '/GetPendingAlert',
        'getenabledalert' => '/GetEnabledAlertTypes'
    ];

    public function __construct() {
        if($this->setTimeZone()) {
        $this->cred = parse_ini_file('cred.ini');
        $this->guzzle = new Client();
        $this->logger = new Logger('log/');
        }
        else{
            echo 'Failed to set time zone!' .PHP_EOL;
            exit();
        }
    }

    /**
     * to login in shoonya 
     * @return bool
     */
    public function login(): bool {
        $this->cred['pwd'] = hash('sha256', utf8_encode($this->cred['pwd']));
        $this->cred['appkey'] = hash('sha256', utf8_encode($this->cred['uid'] . '|' . $this->cred['appkey']));
        $totp = \OTPHP\TOTP::create($this->cred['totp']);
        $this->cred['factor2'] = $totp->now();
        $req = $this->request('login', $this->cred, false);
        if ($this->log($req, ['logged in successfully!', 'falied to loggedin!'])) {
            $this->sessionData($req);
            return true;
        }
        return false;
    }

    /**
     * to logout from shoonya
     * @return bool
     */
    public function logout(): bool {
        $req = $this->request('logout', ['ordersource' => 'API', 'uid' => $this->uid]);
        if ($this->log($req, ['logout successfully!', 'failed to logout!'])) {
            $this->jKey = null;
            $this->userName = null;
            $this->accountId = null;
            $this->uid = null;
            return true;
        }
        return false;
    }

    /**
     * to reset the password
     * @param string $uid
     * @param string $pan
     * @param string $dob
     * @return bool
     */
    public function forgotPassword(string $uid, string $pan, string $dob): bool {
        $values = [
            'source' => 'API',
            'uid' => $uid,
            'pan' => $pan,
            'dob' => $dob
        ];
        $req = $this->request('forgot_password', $values, false);
        return $this->log($req, ['Password Changed Successfully', 'Could not changed password!']);
    }

    /**
     * get the watchlist names
     * @return array|bool
     */
    public function getWatchListNames(): array|bool {
        $req = $this->request('watchlist_names', ['ordersource' => 'API', 'uid' => $this->uid]);
        if ($this->log($req, ['wlN data fatched successfully', 'failed to fecth wlN data'])) {
            return $req->values;
        }
        return false;
    }

    /**
     * get watchlist 
     * @param string $wlname
     * @return array|bool
     */
    public function getWatchList(string $wlname): array|bool {
        $req = $this->request('watchlist', ['ordersource' => 'API', 'uid' => $this->uid, 'wlname' => (string) $wlname]);
        if ($this->log($req, ['wl data fatched successfully', 'failed to fecth wl data'])) {
            return $req->values;
        }
        return false;
    }

    /**
     * add scrip to watchlist
     * @param string $wlname
     * @param string|array $instrument
     * @return boolean
     */
    public function addScripWatchList(string $wlname, string|array $instrument) {
        $values = ['ordersource' => 'API', 'uid' => $this->uid, 'wlname' => $wlname];
        if (is_array($instrument)) {
            $values['scrips'] = implode('#', $instrument);
        } else {
            $values['scrips'] = $instrument;
        }
        $req = $this->request('watchlist_add', $values);
        if ($this->log($req, ['scrip added successfully', 'failed to add in wl'])) {
            return $req;
        }
        return false;
    }

    /**
     * delete scrip from watchlist
     * @param string $wlname
     * @param string|array $instrument
     * @return boolean
     */
    public function deleteScripWatchList(string $wlname, string|array $instrument) {
        $values = ['ordersource' => 'API', 'uid' => $this->uid, 'wlname' => $wlname];
        if (is_array($instrument)) {
            $values['scrips'] = implode('#', $instrument);
        } else {
            $values['scrips'] = $instrument;
        }
        $req = $this->request('watchlist_delete', $values);
        if ($this->log($req, ['scrip deleted successfully', 'failed to delete from wl'])) {
            return $req;
        }
        return false;
    }

    /**
     * search for scrip
     * @param string $searchtext
     * @param string $exchange
     * @return array
     */
    public function searchScrip(string $searchtext, string $exchange = 'BSE'): array {
        
        $values = [
            'uid' => $this->uid,
            'exch' => $exchange,
            'stext' => $searchtext
        ];
        return $this->request('searchscrip', $values)->values;
    }

    /**
     * get scrip's token 
     * @param string $tysm
     * @param string $exch
     * @return string
     */
    public function getToken(string $tysm, string $exch = 'NFO'): string {
        $searchScrip = $this->searchScrip($tysm, $exch);
        return (string) $searchScrip[0]->token;
    }

    /**
     * get ltp of scrip
     * @param string $tysm
     * @param string $exch
     * @return type
     */
    public function getLTP(string $tysm, string $exch = 'BSE') {
        $ltp = $this->getQuotes($tysm, $exch);
        return $ltp->lp;
    }

    /**
     * 
     * @param string $prd
     * @param string $seg
     * @param string $exch
     * @return type
     */
    public function getLimits($prd = null, $seg = null, $exch = null) {
        $values = [
            'uid' => $this->uid,
            'actid' => $this->accountId
        ];

        if (!is_null($prd)) {
            $values['prd'] = $prd;
        }
        if (!is_null($seg)) {
            $values['seg'] = $seg;
        }
        if (!is_null($exch)) {
            $values['exch'] = $exch;
        }
        return $this->request('limits', $values);
    }

    /**
     * get option chain
     * @param string $tsym
     * @param int $strprc
     * @param int $count
     * @param string $exch
     * @return array
     */
    public function getOptionChain(string $tsym, int $strprc, int $count = 5, string $exch = 'NFO'):array {
        $values = [
            'uid' => $this->uid,
            'exch' => $exch,
            'tsym' => $tsym,
            'strprc' => "$strprc",
            'cnt' => "$count"
        ];
        $oc = $this->request('optionchain', $values);
        foreach ($oc->values as $key => $ocData) {
            if ($ocData->optt == 'CE') {
                $quotes = $this->getQuotes($ocData->token, 'NFO');
                $ceData[$ocData->strprc] = ['CE', $ocData->token, $ocData->tsym,$quotes->lp,$quotes->oi,$ocData->strprc];
            }
            if ($ocData->optt == 'PE') {
                $quotes = $this->getQuotes($ocData->token, 'NFO');
                $peData[$ocData->strprc] = [$quotes->oi,$quotes->lp,$ocData->tsym, $ocData->token,'PE'];
            }
        }

        $data = array_merge_recursive($ceData, $peData);
        ksort($data);
        return $data;
    }
    
    /**
     * get scrip exchange token based on local database
     * @param string $scrip
     * @param string $exch
     * @return type
     */
    public function getScripToken(string $scrip,string $exch = 'BSE') {
        if ($exch == 'BSE') {
            $tkNum = parse_ini_file('scrip/bse.ini');
            $token = $tkNum[$scrip];
        }
        if ($exch == 'NSE') {
            $tkNum = parse_ini_file('scrip/nse.ini');
            $token = $tkNum[$scrip];
        }
        return $token;
    }
    

    /**
     * get scrip info 
     * @param string $token
     * @param string $exch
     */
    public function getScripInfo(string $token, string $exch = 'BSE') {
        $tkNum = $this->getScripToken($token,$exch);
        $values = [
            'uid' => $this->uid,
            'exch' => $exch,
            'token' => $tkNum
        ];
        return $this->request('scripinfo', $values);
    }

    /**
     * get running quotes of scrip
     * @param string $token
     * @param string $exchange
     * @return array
     */
    public function getQuotes(string $token, string $exchange = 'BSE') {
        if ($exchange == 'BSE') {
            $tkNum = parse_ini_file('scrip/bse.ini');
            $token = ctype_lower($token) ? $tkNum[$token] : $tkNum[strtolower($token)];
        }
        if ($exchange == 'NSE') {
            $tkNum = parse_ini_file('scrip/nse.ini');
            $token = $tkNum[$token];
        }

        $values = [
            'uid' => $this->accountId,
            'exch' => $exchange,
            'token' => $token
        ];
        return $this->request('getquotes', $values);
    }

    /**
     * get time series based price data in minutes
     * @param string $token
     * @param string $startTime d-m-Y
     * @param string $endTime
     * @param string $interval 1, 3, 5 , 10, 15, 30, 60, 120, 240
     * @param string $exch
     */
    public function getTimePriceSeries(string $token, string $startTime = null, string $endTime = null, string $interval = '15', string $exch = 'BSE') {
        if ($exch == 'BSE') {
            $tkNum = parse_ini_file('scrip/bse.ini');
        }
        if ($exch == 'NSE') {
            $tkNum = parse_ini_file('scrip/nse.ini');
        }

        $values = [
            'ordersource' => 'API',
            'uid' => $this->accountId,
            'exch' => $exch,
            'token' => $tkNum[$token]
        ];
        
         if (is_null($startTime)) {
            $values['st'] = (string) strtotime(date('d-m-Y h:i:s'));
        } else {
            $values['st'] = (string) strtotime($startTime);
        }
        if (is_null($endTime)) {
            $values['et'] = (string) strtotime(date('d-m-Y h:i:s')); 
        }
        else {
            $values['et'] = (string) strtotime($endTime);
        }
        
        $values['intrv'] = (string) $interval;
        
        return $this->request('TPSeries', $values);
    }

    /**
     * get eod price series data
     * @param string $tysm
     * @param string $startDate
     * @param string $endDate
     * @param string $exch
     * @return type
     */
    public function getDailyPriceSeries(string $tysm, string $startDate, string $endDate = null, string $exch = 'NSE') {
      
        if (is_null($endDate)) {
            $et = (string) strtotime(date('d-m-Y 15:30:00'));
        } else {
            $et = strtotime($endDate);
        }
        $st =  strtotime($startDate);

        $values = [
            'ordersource' => 'API',
            'uid' => $this->accountId,
            'sym' => " $exch : $tysm",
            'from' => $st,
            'to' => $et
        ];
        
         $request = $this->guzzle->post($this->urls['eodhost'], [
          'header' => ['Content-Type' => 'application/json'],
            'body' => json_encode($values)
        ]);
        return $this->decode($request->getBody());
    }
    
    /**
     * Coverts a day or carry-forward position from one product to another.
     * @param string $exch
     * @param string $tsym
     * @param int $qty
     * @param string $newPrd
     * @param string $prevPrd
     * @param string $tranType
     * @param string $posType
     */
    public function positionProductConversion(string $exch,string $tsym, int $qty , string $newPrd, string $prevPrd, string $tranType, string $posType) {
        $values = [
            'ordersource' => 'API',
            'uid' => $this->accountId,
            'actid' => $this->accountId,
            'exch' => $exch,
            'tsym' => $tsym,
            'qty' =>  "$qty",
            'prd' => $newPrd,
            'prevprd' => $prevPrd,
            'trantype' => $tranType,
            'postype' => $posType
        ];
        
        return $this->request('product_conversion', $values);
    }

    /**
     * get single order history
     * @param int $orderNo
     * @return type
     */
    public function singleOrderHistory(int $orderNo) {
        return $this->request('singleorderhistory', ['uid' => $this->uid, 'norenordno' => $orderNo]);
    }

    /**
     * get order book
     * @return array
     */
    public function getOrderbook() {
        return $this->request('orderbook', ['uid' => $this->uid]);
    }

    /**
     * get trade book
     * @return array|bool
     */
    public function getTradebook() {
        return $this->request('tradebook', ['uid' => $this->uid, 'actid' => $this->accountId]);
    }

    /**
     * get portfolio holdings
     * @param string $productType
     * @return array | stdClass
     */
    public function getHoldings($productType = self::Delivery) {
        return $this->request('holdings', ['uid' => $this->uid, 'actid' => $this->accountId, 'prd' => $productType]);
    }

    /**
     * 
     * @return array | stdClass
     */
    public function getPositions() {
        return $this->request('positions', ['uid' => $this->uid, 'actid' => $this->accountId]);
    }

    /**
     * to place diffrent type of orders 
     * @param type $buy_or_sell
     * @param type $productType
     * @param type $exchange
     * @param type $tradingSymbol
     * @param type $quantity
     * @param type $discloseQty
     * @param type $priceType
     * @param int $price
     * @param int $triggerPrice
     * @param type $retention
     * @param type $amo
     * @param type $remarks
     * @param int $booklossPrice
     * @param int $bookprofitPrice
     * @param int $trailPrice
     * @return boolean
     */
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
            'tsym' => $tradingSymbol,
            'qty' => (string) ($quantity),
            'dscqty' => (string) ($discloseQty),
            'prctyp' => $priceType,
            'prc' => (string) ($price),
            'trgprc' => (string) ($triggerPrice),
            'ret' => $retention,
            'remarks' => $remarks,
            'amo' => $amo
        ];

        #if cover order or high leverage order
        if ($productType == 'H') {
            $values["blprc"] = (string) ($booklossPrice);
            #trailing price
            if ($trailPrice != 0.0) {
                $values["trailprc"] = (string) ($trailPrice);
            }
        }

        #bracket order
        if ($productType == 'B') {
            $values["blprc"] = (string) ($booklossPrice);
            $values["bpprc"] = (string) ($bookprofitPrice);
            #trailing price
            if ($trailPrice != 0.0) {
                $values["trailprc"] = (string) ($trailPrice);
            }
        }
        $req = $this->request('placeorder', $values);
        if ($this->log($req, ["order placed, ON:- $req->norenordno", 'failed to place order!'])) {
            $this->orderBook['orderID'] = $req->norenordno;
            $this->orderBook['orderID']['tysm'] = $tradingSymbol;
            $this->orderBook['orderID']['time'] = date('h:i:s');
            $this->orderBook['orderID']['date'] = date('d-m-y');
            return (string) $req->norenordno;
        }
        return false;
    }

    /**
     * get the placed order status
     * @param string $orderNo
     * @return boolean
     */
    public function getOrderStatus($orderNo) {
        $orderHistory = $this->singleOrderHistory("$orderNo");
        if ($orderHistory->status == 'COMPLETE') {
            $this->orderBook[$orderNo]['status'] = 'C';
            $this->orderBook[$orderNo]['Qty'] = 1;
            $this->orderBook[$orderNo]['ap'] = $orderHistory->avgprc;
            return true;
        }
        return false;
    }

    /**
     * to modify placed order
     * @param type $orderNo
     * @param type $exchange
     * @param type $tradingSymbol
     * @param type $newquantity
     * @param type $newpriceType
     * @param type $newprice
     * @param type $newtriggerPrice
     * @param type $booklossPrice
     * @param type $bookprofitPrice
     * @param type $trailPrice
     * @return boolean
     */
    public function modifyOrder($orderNo, $exchange, $tradingSymbol, $newquantity,
            $newpriceType, $newprice = 0.0, $newtriggerPrice = null, $booklossPrice = 0.0, $bookprofitPrice = 0.0, $trailPrice = 0.0) {
        $values = ['ordersource' => 'API',
            'uid' => $this->uid,
            'actid' => $this->accountId,
            'norenordno' => (string) ($orderNo),
            'exch' => $exchange,
            'tsym' => ($tradingSymbol),
            'qty' => (string) ($newquantity),
            'prctyp' => $newpriceType,
            'prc' => (string) ($newprice)
        ];
        if ($newpriceType == self::PrinceSLLmit || $newpriceType == self::PriceSLM) {
            if ($newtriggerPrice != null) {
                $values['trgprc'] = (string) ($newtriggerPrice);
            } else {
                $this->log(null, ['', 'trigger price is missing!']);
                return false;
            }
        }
        if ($booklossPrice != 0.0) {
            $values['blprc'] = (string) ($booklossPrice);
        }
        if ($trailPrice != 0.0) {
            $values['trailprc'] = (string) ($trailPrice);
        }
        if ($bookprofitPrice != 0.0) {
            $values['bpprc'] = (string) ($bookprofitPrice);
        }
        $req = $this->request('modifyorder', $values);
        if ($this->log($req, ["order modified, ON:- $orderNo", 'failed to modify order!'])) {
            return $req;
        }
        return false;
    }

    /**
     * cancle placed order
     * @param type $orderNo
     * @return boolean
     */
    public function cancelOrder($orderNo) {
        $values = ['ordersource' => 'API',
            'uid' => $this->uid,
            'norenordno' => (string) ($orderNo)
        ];
        $req = $this->request('cancelorder', $values);
        if ($this->log($req, ["order canceled, ON:- $orderNo", 'failed to cancel order!'])) {
            return $req;
        }
        return false;
    }

    /**
     * close/exit from position
     * @param type $orderNo
     * @param type $productType
     * @return boolean
     */
    public function exitOrder($orderNo, $productType) {
        $values = ['ordersource' => 'API',
            'uid' => $this->uid,
            'actid' => $this->accountId,
            'norenordno' => (string) ($orderNo),
            'prd' => $productType
        ];
        $req = $this->request('exitorder', $values);
        if ($this->log($req, ["order canceled, ON:- $orderNo", 'failed to cancel order!'])) {
            return $req;
        }
        return false;
    }

    /**
     * set gtt or gtc order 
     * @param string $buy_or_sell
     * @param string $productType 
     * @param string $exchange  BSE|NSE|NFO|MCX
     * @param string $tradingSymbol 
     * @param float $priceToCompare 
     * @param int $quantity 
     * @param float $price 
     * @param string $ai_t 
     * @param string $retention DAY|EOS|IOC
     * @param string $remarks
     * @param int $discloseQty = null
     * @return boolean
     */
    public function gttOrder(string $buy_or_sell, string $productType, string $exchange, string $tradingSymbol, float $priceToCompare,
            int $quantity, float $price = 0, string $ai_t = self::AITG, string $retention = 'DAY', string $remarks = null, $discloseQty = null) {
        if ($remarks == null) {
            $remarks = "gtt for $tradingSymbol placed on " . (string) date('h:i:s');
        }
        $values = ['ordersource' => 'API',
            'uid' => $this->uid,
            'actid' => $this->accountId,
            'trantype' => $buy_or_sell,
            'prd' => $productType,
            'exch' => $exchange,
            'd' => (string) $priceToCompare,
            'validity' => 'GTT',
            'tsym' => $tradingSymbol,
            'qty' => (string) $quantity,
            'dscqty' => (string) $discloseQty,
            'prctyp' => self::PriceLimit,
            'prc' => (string) $price,
            'ret' => $retention,
            'remarks' => $remarks,
            'validity' => 'GTT',
            'ai_t' => $ai_t
        ];
        $req = $this->request('placegttorder', $values);
        $this->log($req, ['gtt order has been placed with OI :- ' . $req->al_id, 'failed to place gtt order!']);
        if ($req) {
            return true;
        }
        return false;
    }

    /**
     * to cancle placed gtt
     * @param int $alID
     * @return boolean
     */
    public function cancelGtt($alID) {
        $req = $this->request('cancelgttorder', ['ordersource' => 'API', 'uid' => $this->uid, 'al_id' => (string) $alID]);
        $this->log($req, ['gtt order has been deleted for OI:- ' . $alID, 'failed to delete gttorder for OI :- ' . $alID]);
        if ($req) {
            return true;
        }
        return false;
    }

    /**
     * get pending gtt order details
     * @return type
     */
    public function getPendingGtt() {
        return $this->request('getpendinggtt', ['ordersource' => 'API', 'uid' => $this->uid]);
    }

    public function getEnableGtt() {
        return $this->request('getenabledgtts', ['ordersource' => 'API', 'uid' => $this->uid]);
    }

    /**
     * get current session tmp data
     * @return Object
     */
    public function getSessionData() {
        return (object)['jKey' => $this->jKey, 'uid' => $this->uid, 'actid' => $this->accountId, 'uname' => $this->userName, $this->exarr, $this->brkname];
    }

    /**
     * ws related  functions
     * 
     */
    
    public function connectWS() {
        $value = [
            't' => 'c',
            'uid' => $this->uid,
            'actid' => $this->accountId,
            'ordersource' => 'API',
            'susertoken' => $this->jKey];
        $this->wsC = new \WSSC\WebSocketClient($this->urls['ws_endpoint'], new \WSSC\Components\ClientConfig());
        $this->wsC->send(json_encode($value));
        print_r($this->wsC->receive());
        if($this->wsC->isConnected()) {
            return true;
        }
        echo 'Failed to connect to WSS' . PHP_EOL;
        return false;
    }

    public function subscribe(array|string $intst, $feedType = self::FeedTouchLine) {
        $values = [];
        $values['t'] = $feedType;
        if (is_array($intst)) {
            $values['k'] = implode('#', $intst);
        } else {
            $values['k'] = $intst;
        }
    }

    public function unsubscribe(array|string $intst, $feedType = self::FeedTouchLine) {
        $values = [];
        if ($feedType == self::FeedTouchLine) {
            $values['t'] = 'u';
        } elseif ($feedType == self::FeedSnapQuoate) {
            $values['t'] = 'ud';
        }

        if (is_array($intst)) {
            $values['k'] = implode('#', $intst);
        } else {
            $values['k'] = $intst;
        }
    }

    /**
     * @todo 
     */
    public function subscribeOrders() {
        $values = ['t' => 'o', 'actid' => $this->accountId];
    }
    
    /**
     *  Set Alert
     * @param string $tsym
     * @param string $ait
     * @param type $d
     * @param string $validity
     * @param string $remarks
     * @param string $exch
     * @return object
     */
    public function setAlert(string $tsym,string $ait, $d, string $validity='DAY', string $remarks=null, string $exch='BSE') {
        $values = ['ordersource' => 'API',
            'uid' => $this->uid,
            'exch' => $exch,
            'd' => "$d",
            'validity' => $validity,
            'tsym' => $tsym,
            'remarks' => $remarks,
            'ai_t' => $ait
        ];
        return $this->request('setalert', $values);
    }
    
    public function getEnabledAlert() {
        $values = ['ordersource' => 'API',
            'uid' => $this->uid
        ];
        
        return $this->request('getenabledalert', $values);
    }


    public function getPendingAlert() {
        $values = ['ordersource' => 'API',
            'uid' => $this->uid
        ];
        
        return $this->request('getpendingalert', $values);
    }
    
    /**
     * 
     * @param  $alertID
     */
    public function modifyAlert($alertID) {
        $values = ['ordersource' => 'API',
            'uid' => $this->uid,
            'ai_t' => $alertID
        ];
        return $this->request('modifyalert', $values);
    }
    
    public function cancleAlert($alertID) {
        $values = ['ordersource' => 'API',
            'uid' => $this->uid,
            'ai_t' => $alertID
        ];
        return $this->request('cancelalert', $values);
    }


    /**
     * send telegram notification
     * @param string $msg
     * @return bool
     */
    public function telegram(string $msg):bool {
        $data = ['chat_id' => $this->cred['ci'],'text'=>$msg];
        $send = $this->decode(file_get_contents("https://api.telegram.org/bot{$this->cred['at']}/sendMessage?" . http_build_query($data)));
        if($send->ok == 'true') {
            echo 'Telegram notification has been sent to ' . $this->cred['ci'] . ' @ ' . date('h:i:m') . PHP_EOL;
            return true;
        }
        return false;
    }
    
    /**
     * to compare two times if t1 < t2 return true else return false
     * @param string $t1
     * @param string $t2
     * @return bool
     */
    public function timeComperison(string $t1,string $t2):bool {
        if(strtotime(date($t1)) < strtotime($t2)) {
            return true;
        }
        return false;
    }

    /**
     * to keep local record of orders
     * @param type $fields
     */
    protected function orderRecords($fields) {
        $csv = new \SplFileObject(date('dmy') . '_Orders,csv', 'a');
        $csv->fputcsv($fields);
        unset($csv);
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

    /**
     * 
     * @param object $req
     * @param array $msg
     * @return boolean
     */
    protected function log($req, array $msg) {
        if (!is_null($req) && ($req->stat == 'Ok' || $req->stat == 'OI created' || $req->stat == 'OI deleted')) {
            $this->logger->info("User {$this->cred['uid']} " . $msg[0]);
            return true;
        }
        $this->logger->notice("User {$this->cred['uid']} " . $msg[1]);
        return false;
    }
    
    protected function decode($jsonData) {
        return json_decode($jsonData);
    }

    protected function request(string $routes, array $jData, $iskey = true) {
        $request = $this->post($this->routes[$routes], $this->jData($jData, $iskey));
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

    protected function jData($data, $isKey = true) {
        if ($isKey) {
            return 'jData=' . json_encode($data) . $this->jKey();
        }
        return 'jData=' . json_encode($data);
    }

    protected function jKey() {
        return '&jKey=' . $this->jKey;
    }
    
    /**
     * 
     * @param string $timezone
     * @return bool
     */
    private function setTimeZone(string $timezone = 'Asia/Kolkata'):bool {
        return date_default_timezone_set($timezone);
    }
}
