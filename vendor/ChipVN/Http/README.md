# PHP Http Client Class

- ChipVN_Http_Client is a simple and powerful class used to sending request, scraping web content and get response like a browser.
- Use 2 functions: cURL, fsockopen, so you can use this class, "curl" **WITHOUT CURL** extension installed

**Note**: _fsockopen_ is faster also it is default. Both methods are the same, but _fsockopen_ limited use proxy feature (only this feature).

* Author    : Phan Thanh Cong <ptcong90@gmail.com>
* Copyright : 2011-2014 Phan Thanh Cong.
* License   : MIT

## Change logs
##### Version 2.6.4: May 27, 2015
* Cookies fixes.

##### Version 2.6.3: May 03, 2015
* Split `execute()` to two methods `executeWithSocket()`, `executeWithCurl()`.

##### Version 2.6.2: Apr 02, 2015
* Add debug mode. use `$client->setDebug(true/false);`

##### Version 2.6.0: Apr 02, 2015
* Fixed parsing cookie with httpOnly, Secure flags
* Fixed following redirect with cookies for domain. (for automatic signing to google)
* Add `$nobody` option to get only headers (helpful to get headers of a video url). Use `$client->setNobody(true/false)`;

##### Version 2.5.8: Oct 07, 2014
* Optimized code
* Added new method `setCookiesPairs()` to set cookies by string key=value; pairs
* Added new method `setRawPostFile()` to post raw content of file

##### Version 2.5.6: Oct 06, 2014
* Changed prepareRequestHeaders() to allows send cookies from sub-domain.

##### Version 2.5.5: Apr 7, 2014
* Optimize and clear code
* Change class name to `ChipVN_Http_Client` (old class name still avaliable and just extends new class name)
* Improve `execute()`
* Sync request data  between Socket and cURL
* Allows unlimit follow redirect by `setFollowRedirect(true, true)`

##### Version 2.5.4: Apr 2, 2014
* Changed class name from ~~\ChipVN\Http\Request~~ to ~~ChipVN_Http_Request~~ to support PHP >= 5.0
* Fixed some bugs while scraping login Flickr.
* Improved followRedirect, parsing/ creating cookies.
* Added new method `resetFollowRedirect()`
* Added dynamic getters, setters (so you may get/set any properties for sending request easier)
* Added new method: `getRedirectedCount()`
* Added new methods: `setCookies()`, `setParameters()`, `setHeaders()`, `removeCookies()`, `removeParameters()`, `removeHeaders()`
* ~~Added new alias methods: `addCookies()`, `addParameters()`, `addHeaders()`~~
* Deprecated methods: `setCookie()`, `setParam()`, `setHeader()` (still avaliable)
* Changed method names: `readBinary()` -> `getFileData()`, `getMimeType()` -> `getFileType()`

##### Version 2.5.3: Apr 1, 2014
* Improve `setCookie()`
* Added new methods `resetResponse()`, `resetRequest()`, `parseCookie()`, `createCookie()`
* Added new method `setFollowRedirect()` to follow redirect
* Added new method `getResponseArrayCookies()` to get all cookies by array [name => [info]]
* Fixed a bug
* Change all properties to protected (need use set* methods to change the properties)

##### Version 2.5: Mar 07, 2014
* ~~Change class name to \ChipVN\Http\Request~~
* Most clean and clear
* Supports composer
* Added new method `setHttpVersion()` to change HTTP protocol version

##### Version 2.4: Jul 25, 2013
* ~~Require PHP 5.3 or newer~~
* Change two static class methods (readBinary, mimeTye) to protected instance method

##### Version 2.3.4: Feb 20, 2013
* Fixed parse headers (typo)

##### Version 2.3.3: Nov 5, 2012
* Re-struct, something edited

##### Version 2.3.2: June 12, 2012
* Add some methods

##### Version 2.3.1: Mar 30, 2012
* Fixed some bugs to work well with PHP 5.3 (E_NOTICE default is enabled)

##### Version 2.3: Feb 2, 2012
* Update for picasa API

##### Version 2.2: Jan 1, 2012
* Support raw data for posting (upload image to picasa)

##### Version 2.1: Dec 23, 2011
* Fixed some bugs

##### Version 2.0: Jun 26, 2011
* Rewrite class to easy use
* Fixed some bugs

##### Version 1.2: April 19, 2011
* Mime-type bug on upload file fixed

##### Version 1.1:
* Supports upload multiple files
* Fixed some bugs

##### Version 1.0:
* Supports send a basic request
* Proxy (only useCurl)
* Supports file uploading

## Usage

Add require `"ptcong/php-http-class": "dev-master"` to _composer.json_ and run `composer update` if you use composer

Create an `ChipVN_Http_Client` instnace

	$client = new ChipVN_Http_Client;

#### Send a request

**Use cURL or fsockopen**

	$client->useCurl(false);

**Set target url** (like to browse a url on browser)

	$client->setTarget('http://google.com');

**Use cookies**

	$client->setCookiesPairs('name=value; name2=value2; name3=value3');

	$client->setCookies('name=value'); // single cookie key=value

	// or
	$client->setCookies('path=/; name2=value2; expires=Tue, 01-Apr-2014 04:57:57 GMT');

	// or
	$client->setCookies(array(
		'name1=value1',
		'name2=value2; expires=Tue, 01-Apr-2014 04:57:57 GMT'
	));

	// or
	$client->setCookies(array(
		'name' => 'name1',
		'value' => 'value1',
		'expires' => 'expires=Tue, 01-Apr-2014 04:57:57 GMT', // not required
		// not required
		// 'path' => '/',
		// 'domain' => null,
		// 'secure' => null,
		// 'httponly' => null
	));

	// or
	$client->setCookies(array(
		array(
			'name' => 'name1',
			'value' => 'value1',
			'expires' => 'expires=Tue, 01-Apr-2014 04:57:57 GMT', // not required
		),
		array(
			'name' => 'name2',
			'value' => 'value2',
			'expires' => 'expires=Tue, 01-Apr-2014 04:57:57 GMT', // not required
		)
	));

**Change HTTP Protocol version**

	$client->setHttpVersion('1.1');

	// or
	$client->setHttpVersion('1.0');

**Follow redirect**

	$client->setFollowRedirect(true);

	// or maximum redirect 5 times. Default is 3 times and return last response
	$client->setFollowRedirect(true, 5);

**Parameters / Uploading file**

	$client->setParameters('name', 'value');

	// or
	$client->setParameters('name=value&name2=value2&name3=value3');

	$client->setParam(array(
		'name1=value1',
		'name2=value2'
	));

	// or
	$client->setParameters(array(
		'name1'  => 'value1',
		'name2'  => 'value2'
	));

	// for uploading
	$client->setParameters('filedata', '@/path/path/file.jpg');

	// also can use
	$client->setParameters(array(
		'filedata'  => '@/path/path/file.jpg'
	));

**Post raw data**

	$client->setRawPost('your data');

**Post raw file**

	$client->setRawPost(file_get_contents('/your/file/path'));

	// but recommend to use the method
	$client->setRawPostFile('/your/file/path');

**Referer**

	$client->setReferer('http://domain.com');

**User Agent**

	$client->setUserAgent('Mozilla/5.0 (Windows NT 6.1; WOW64; rv : 9.0.1) Gecko/20100101 Firefox/9.0.1');

**Connect timeout**

	$client->setTimeout($seconds);

**Method**

	$client->setMethod('POST');
	$client->setMethod('GET');
	$client->setMethod('PUT');
	$client->setMethod('HEAD');
	// etc

**Submit type**

	// use to upload file
	$client->setSubmitMultipart();

	// submit normal form
	$client->setSubmitNormal();

**Request enctype**

	$client->setEnctype('application/x-www-form-urlencoded');

**Use Headers**

	$client->setHeaders('Origin', 'xxx');

	// or
	$client->setHeaders('User-Agent: Firefox/9.0.1');

	// or
	$client->setHeaders(array(
		'name1=value1',
		'name2=value2',
	));

	// or
	$client->setHeaders(array(
		'name1'  => 'value1',
		'name2'  => 'value2'
	));

**Use Proxy**
The method only avaliable if you use cURL for sending request

	$client->setProxy('127.0.0.1:80');

	// or
	$client->setProxy('127.0.0.1:80', $username, $password);

**WWW-Authenticate**

	$client->setAuth('user', 'pass');

**Remove cookies/ parameters/ headers added**

	$client->removeHeaders(true); // remove all headers
	$client->removeHeaders('Referer'); // remove a header

	$client->removeCookies(true); // remove all cookies
	$client->removeCookies('name'); // remove a cookie

	$client->removeParameters(true); // remove all parameters
	$client->removeParameters('name'); // remove a parameter

#### Helpers

**parseCookie()**:

	$cookie = $client->parseCookie('gostep=1; expires=Tue, 01-Apr-2014 05:20:23 GMT; Max-Age=300; path=/; domain=domain.com; secure;');

	print_r($cookie);

	[gostep] => Array
    (
        [expires] => 'Tue, 01-Apr-2014 05:20:23 GMT'
        [Max-Age] => '300'
        [path] => '/''
        [name] => 'gostep'
        [value] => '1'
        [domain] => 'domain.com'
        [secure] => true
        [httponly] => null
    )

**createCookie()**
This method used to create cookie from array with keys like above (parseCookie) to string

**Execute sending request**

	$boolean = $client->execute();

	var_dump($client->errors); // if have


#### Get Response

**Get response status code**

	echo $client->getResponseStatus();

**Get response headers**

	print_r($client->getResponseHeaders());

	// or
	echo $client->getResponseHeaders('location');

	// or "set-cookie" return an array if have.
	print_r($client->getResponseHeaders('set-cookie'));

**Get response cookies**

	// by string
	echo $client->getResponseCookies();

	// by array [name => [info]]
	print_r($client->getResponseArrayCookies()); // get all cookies

	print_r($client->getResponseArrayCookies('cookie-name'));

**Get response body text**

	echo $client->getResponseText();

**Reset request**
Before sending another request, instead of create a new instance. Just call

	$client->reset();

or only reset response data and keep old request data

	$client->resetResponse();
