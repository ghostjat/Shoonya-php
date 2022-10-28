[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ghostjat/Shoonya-php/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/ghostjat/Shoonya-php/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/ghostjat/Shoonya-php/badges/build.png?b=main)](https://scrutinizer-ci.com/g/ghostjat/Shoonya-php/build-status/main)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/ghostjat/Shoonya-php/badges/code-intelligence.svg?b=main)](https://scrutinizer-ci.com/code-intelligence)
![GitHub contributors](https://img.shields.io/github/contributors/ghostjat/Shoonya-php)
![GitHub commit activity](https://img.shields.io/github/commit-activity/m/ghostjat/Shoonya-php)
![GitHub last commit](https://img.shields.io/github/last-commit/ghostjat/Shoonya-php)
![Packagist Version](https://img.shields.io/packagist/v/ghostjat/Shoonya-php)
![GitHub code size in bytes](https://img.shields.io/github/languages/code-size/ghostjat/Shoonya-php)
![GitHub top language](https://img.shields.io/github/languages/top/ghostjat/Shoonya-php)

# A LOT OF THANKS TO:
 
Finavsia https://prism.finvasia.com/register/?franchiseLead=OTA1ODY=

# Shoonya-php (unofficial)

This php library used to connect to Finvasia Shoonya OMS.

This is a php wrapper or connector to the RestAPI and websocket of Shoonya. 


****

## Install

to install this package please use Composer 

``` composer require ghostjat/Shoonya-php ```

****

## Shoonya-php API 

`  public  __construct()`

`  public  login(): bool`  to login in shoonya

`  public  logout(): bool` to logout from shoonya

`  public  forgotPassword(string $uid, string $pan, string $dob): bool` to reset the password

`  public  getWatchListNames(): array|bool`   get the watchlist names

`  public  getWatchList(string $wlname): array|bool`  get watchlist

`  public  addScripWatchList(string $wlname, string|array $instrument): boolean`  add scrip to watchlist

`  public  deleteScripWatchList(string $wlname, string|array $instrument): boolean`  delete scrip from watchlist

`  public  searchScrip(string $searchtext, string $exchange = 'BSE'): array` search for scrip

`  public  getToken(string $tysm, string $exch = 'NFO'): string` get scrip's token

`  public  getLTP(string $tysm, string $exch = 'NFO')` get ltp of scrip

`  public  getLimits(string $prd = null, string $seg = null, string $exch = null)`

`  public  getOptionChain(string $tsym, int $strprc, int $count = 5, string $exch = 'NFO'): array` get option chain

`  public  getScripInfo(string $token, string $exch = 'BSE')` get scrip info

`  public  getQuotes(string $token, string $exchange = 'BSE'): array` get running quotes of scrip

`  public  getTimePriceSeries( string $token, string $startTime = null, string
$endTime = null, string $interval = '15', string $exch = 'BSE' )` get time series based price data in minutes

`  public  getDailyPriceSeries(string $tysm, string $startDate, string $endDate =
null, string $exch = 'NSE') ` get eod price series data

`  public  positionProductConversion()`

`  public  singleOrderHistory(int $orderNo) ` get single order history

`  public  getOrderbook(): array` get order book

`  public  getTradebook(): array|bool` get trade book

`  public  getHoldings(string $productType = self::Delivery): array|object` get portfolio holdings

`  public  getPositions(): array|stdClass`

`  public  placeOrder( type $buy_or_sell, type $productType, type $exchange, type
$tradingSymbol, type $quantity, type $discloseQty, type $priceType, int
$price = 0.0, int $triggerPrice = null, type $retention = 'DAY', type
$amo = 'NO', type $remarks = null, int $booklossPrice = 0.0, int
$bookprofitPrice = 0.0, int $trailPrice = 0.0, ): boolean` to place diffrent type of orders

`  public  getOrderStatus(string $orderNo): boolean` get the placed order status

`  public  modifyOrder( type $orderNo, type $exchange, type $tradingSymbol, type
$newquantity, type $newpriceType, type $newprice = 0.0, type
$newtriggerPrice = null, type $booklossPrice = 0.0, type
$bookprofitPrice = 0.0, type $trailPrice = 0.0, ): boolean` to modify placed order

`  public  cancelOrder(type $orderNo): boolean` cancle placed order

`  public  exitOrder(type $orderNo, type $productType): boolean` close/exit from position

`  public  gttOrder( string $buy_or_sell, string $productType, string $exchange,
string $tradingSymbol, float $priceToCompare, int $quantity, float
$price = 0, string $ai_t = self::AITG, string $retention = 'DAY', string
$remarks = null, int $discloseQty = null, ): boolean` set gtt or gtc order

`  public  cancelGtt(int $alID): boolean` to cancle placed gtt

`  public  getPendingGtt() ` get pending gtt order details

`  public  getEnableGtt()`

`  public  getSessionData(): array ` get current session tmp data

`  public  subscribe(array|string $intst, $feedType = self::FeedTouchLine)` ws related functions

`  public  unsubscribe(array|string $intst, $feedType = self::FeedTouchLine)`

`  public  subscribeOrders()`

`  public  telegram(string $msg): bool` send telegram notification

****

## Todo

AAB PMS RLAB Live-Algo Algo-Backtesting

## Author

 @author Shubham Chaudhary

 @author  https://www.linkedin.com/in/drshubh/

 @blog https://ghostjat.medium.com

 @since Aug 2022

 @version 1.0.1

 @license MIT

****

## License

Copyright (C) 2022 Shubham Chaudhary- All Rights Reserved.

****
