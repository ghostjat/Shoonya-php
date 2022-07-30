<?php

namespace Core\Api;

use GuzzleHttp\Client;

class Shoonya {

    protected $guzzle, $jKey, $userName, $accountId, $pwd, $uid;
    protected $cred,$orderNo = [];
    protected const Delivery = 'C', Intraday = 'I', Normal = 'M', CF = 'M';

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
     * @throws ErrorException
     */
    public function login():bool {
        $this->cred['pwd'] = hash('sha256', utf8_encode($this->cred['pwd']));
        $this->cred['appkey'] = hash('sha256', utf8_encode($this->cred['uid'] . '|' . $this->cred['appkey']));
        $request = $this->post($this->urls['endpoint'], $this->routes['login'], $this->jData($this->cred));
        $decode = $this->decode($request->getBody());
        if ($decode->stat != 'Ok') {
            throw new ErrorException($decode->emsg, $request->getStatusCode(), 'LoginError');
        }
        $this->jKey = $decode->susertoken;
        $this->userName = $decode->actid;
        $this->accountId = $decode->actid;
        $this->uid = $this->cred['uid'];
        return true;
    }

    /**
     * 
     * @return bool
     * @throws ErrorException
     */
    public function logout() :bool {
        $body = $this->jData(['ordersource' => 'API', 'uid' => $this->uid]) . $this->jKey();
        $request = $this->post($this->urls['endpoint'], $this->routes['logout'], $body);
        $decode = $this->decode($request->getBody());
        if ($decode->stat != 'Ok') {
            throw new ErrorException($decode->emsg, $request->getStatusCode(), 'LogoutError');
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
     * @throws ErrorException
     */
    public function searchScrip(string $exchange, string $searchtext):array {
        if ($searchtext == null) {
            throw new ErrorException('search text cannot be null');
        }

        $values = [
            'uid' => $this->uid,
            'exch' => $exchange,
            'stext' => ($searchtext) // urllib . parse . quote_plus
        ];

        $body = $this->jData($values) . $this->jKey();

        $request = $this->post($this->urls['endpoint'], $this->routes['searchscrip'], $body);
        $decode = $this->decode($request->getBody());
        if ($decode->stat != 'Ok') {
            throw new ErrorException($decode->emsg, $request->getStatusCode(), 'ScripSearch-Error');
        }
        return $decode->values;
    }

    /**
     * 
     * @param string $productType
     * @return array
     * @throws ErrorException
     */
    public function getHoldings($productType = self::Delivery):array {

        $values = [
            'uid ' => $this->userName,
            'actid' => $this->accountId,
            'prd' => $productType
        ];
        
        $body = $this->jData($values) . $this->jKey();
        $request = $this->post($this->urls['endpoint'], $this->routes['holdings'], $body);
        $decode = $this->decode($request->getBody());
        if ($decode->stat != 'Ok') {
            throw new ErrorException($decode->emsg, $request->getStatusCode(), 'GetHolding-Error');
        }
        return $decode;
    }
    
    /**
     * 
     * @return array
     * @throws ErrorException
     */
    public function getPositions():array{
        $values = [
            'uid ' => $this->userName,
            'actid' => $this->accountId
        ];
        $body = $this->jData($values).$this->jKey();
        $request = $this->post($this->urls['endpoint'], $this->routes['positions'], $body);
        $decode = $this->decode($request->getBody());
        if ($decode->stat != 'Ok') {
            throw new ErrorException($decode->emsg, $request->getStatusCode(), 'GetPosition-Error');
        }
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

        $body = $this->jData($values) . $this->jKey();
        $request = $this->post($this->urls['endpoint'], $this->routes['placeorder'], $body);
        $decode = $this->decode($request->getBody());
        if ($decode->stat != 'Ok') {
            throw new ErrorException($decode->emsg, $request->getStatusCode(), 'OrderPlacing-Error');
        }
        $this->orderNo[] = $decode->norenordno;
        return true;
    }

    protected function decode($jsonData) {
        return json_decode($jsonData);
    }

    protected function post($urls, $routes, $body, $contentType = 'application/json') {
        return $this->guzzle->post($urls . $routes, [
                    'header' => ['Content-Type' => $contentType],
                    'body' => $body
        ]);
    }

    protected function jData($data) {
        return 'jData=' . json_encode($data);
    }

    protected function jKey() {
        return '&jKey=' . $this->jKey;
    }

}
