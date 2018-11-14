# Simple ugly proxy for Google Apps Scripts (in PHP)

This script was created for a [Crypto-Currency Portfolio Tracking Sheet](https://docs.google.com/spreadsheets/d/1eBOIR0Wg-baEyUi5ll0VjUJo4bRa1FoA3d1aP6zECok/edit?usp=sharing). But you can use it for any Google Drive file that is requesting data from an API (REST, etc.) which is limited to certain requests per IP.

Simply set you token and proxy URL and request data via the following method:
```Javascript
function fetchUrl(url) {
  var finalUrl;

  var proxyUrl = 'http://api.yourdomain.com/proxy.php'; // TODO Enter the complete URL to your proxy script e.g. 
  var proxyToken = ''; // TODO Enter your token
   
  if(proxyUrl==='') {
    finalUrl = url;
  } else {
    finalUrl = proxyUrl + "?url=" + encodeURIComponent(url) + "&token=" + encodeURIComponent(proxyToken); 
  }
  
  var response = UrlFetchApp.fetch(finalUrl);
  var returnText = response.getContentText();
  return returnText;
}
```

You can use it like:
```Javascript
  var returnText = fetchUrl('https://min-api.cryptocompare.com/data/price?fsym=ETH&tsyms=BTC,USD,EUR');
  
  // Do anything with your web request result like parsing it if it's JSON....:
  var parsedData = JSON.parse(returnText);
```