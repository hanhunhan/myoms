<?php
/**
 * SAE����ץȡ����
 *
 * @author  zhiyong
 * @version $Id: SaeFetchurl.class.php 2766 2012-02-20 15:58:21Z luofei614@gmail.com $
 * @package sae
 *
 */

/**
 * SAE����ץȡclass
 *
 * SaeFetchurl����ץȡ�ⲿ���ݡ�֧�ֵ�Э��Ϊhttp/https��<br />
 * �����ѱ���������ֱ��ʹ��curlץȡ�ⲿ��Դ
 * @deprecated �����ѱ���������ֱ��ʹ��curlץȡ�ⲿ��Դ
 *
 * Ĭ�ϳ�ʱʱ�䣺
 *  - ���ӳ�ʱ�� 5��
 *  - �������ݳ�ʱ�� 30��
 *  - �������ݳ�ʱ�� 40��
 *
 * ץȡҳ��
 * <code>
 * $f = new SaeFetchurl();
 * $content = $f->fetch('http://sina.cn');
 * </code>
 *
 * ����POST����
 * <code>
 * $f = new SaeFetchurl();
 * $f->setMethod('post');
 * $f->setPostData( array('name'=> 'easychen' , 'email' => 'easychen@gmail.com' , 'file' => '�ļ��Ķ���������') );
 * $ret = $f->fetch('http://photo.sinaapp.com/save.php');
 * 
 * //ץȡʧ��ʱ���������ʹ�����Ϣ
 * if ($ret === false)
 * 		var_dump($f->errno(), $f->errmsg());
 * </code>
 *
 * ������ο���
 *  - errno: 0 		�ɹ�
 *  - errno: 600 	fetchurl �����ڲ�����
 *  - errno: 601 	accesskey ������
 *  - errno: 602 	��֤���󣬿�����secretkey����
 *  - errno: 603 	����fetchurl��ʹ�����
 *  - errno: 604 	REST Э�������ص�header�����ڻ��������󣬽���ʹ��SAE�ṩ��fetch_url����
 *  - errno: 605 	�����URI��ʽ���Ϸ�
 *  - errno: 606 	�����URI�����������ɴ
 *
 * @author  zhiyong
 * @version $Id: SaeFetchurl.class.php 2766 2012-02-20 15:58:21Z luofei614@gmail.com $
 * @package sae
 *
 */
class SaeFetchurl extends SaeObject
{
	function __construct( $akey = NULL , $skey = NULL )
	{
		if( $akey === NULL )
			$akey = SAE_ACCESSKEY;

		if( $skey === NULL )
			$skey = SAE_SECRETKEY;

		$this->impl_ = new FetchUrl($akey, $skey);
		$this->method_ = "get";
		$this->cookies_ = array();
		$this->opt_ = array();
		$this->headers_ = array();
	}

	/**
	 * ����acccesskey��secretkey
	 *
	 * ʹ�õ�ǰ��Ӧ�õ�keyʱ,����Ҫ���ô˷���
	 *
	 * @param string $akey
	 * @param string $skey
	 * @return void
	 * @author zhiyong
	 * @ignore
	 */
	public function setAuth( $akey , $skey )
	{
		$this->impl_->setAccesskey($akey);
		$this->impl_->setSecretkey($skey);
	}

	/**
	 * @ignore
	 */
	public function setAccesskey( $akey )
	{
		$this->impl_->setAccesskey($akey);
	}

	/**
	 * @ignore
	 */
	public function setSecretkey( $skey )
	{
		$this->impl_->setSecretkey($skey);
	}

	/**
	 * ��������ķ���(POST/GET/PUT... )
	 *
	 * @param string $method
	 * @return void
	 * @author zhiyong
	 */
	public function setMethod( $method )
	{
		$this->method_ = trim($method);
		$this->opt_['method'] = trim($method);
	}

	/**
	 * ����POST����������
	 *
	 * @param array|string $post_data ����ʽΪarrayʱ��keyΪ��������,valueΪ����ֵ��ʹ��multipart��ʽ�ύ������ʽΪstringʱ��ֱ����Ϊpost��content�ύ����curl_setopt($ch, CURLOPT_POSTFIELDS, $data)��$data�ĸ�ʽ��ͬ��
	 * @param bool $multipart value�Ƿ�Ϊ����������
	 * @return bool
	 * @author zhiyong
	 */
	public function setPostData( $post_data , $multipart = false )
	{
		$this->opt_["post"] = $post_data;
		$this->opt_["multipart"] = $multipart;

		return true;
	}

	/**
	 * �ڷ����������,�������ͷ
	 *
	 * ������ʹ�ô˷����趨��ͷ��
	 *  - Content-Length
	 *  - Host
	 *  - Vary
	 *  - Via
	 *  - X-Forwarded-For
	 *  - FetchUrl
	 *  - AccessKey
	 *  - TimeStamp
	 *  - Signature
	 *  - AllowTruncated	//��ʹ��setAllowTrunc�����������趨
	 *  - ConnectTimeout	//��ʹ��setConnectTimeout�����������趨
	 *  - SendTimeout		//��ʹ��setSendTimeout�����������趨
	 *  - ReadTimeout		//��ʹ��setReadTimeout�����������趨
	 *
	 *
	 * @param string $name
	 * @param string $value
	 * @return bool
	 * @author zhiyong
	 */
	public function setHeader( $name , $value )
	{
		$name = trim($name);
		if (!in_array(strtolower($name), FetchUrl::$disabledHeaders)) {
			$this->headers_[$name] = $value;
			return true;
		} else {
			trigger_error("Disabled FetchUrl Header:" . $name, E_USER_NOTICE);
			return false;
		}
	}

	/**
	 * ����FetchUrl����
	 *
	 * �����б�
	 *  - truncated		����		�Ƿ�ض�
	 *  - redirect			����		�Ƿ�֧���ض���
	 *  - username			�ַ���		http��֤�û���
	 *  - password			�ַ���		http��֤����
	 *  - useragent		�ַ���		�Զ���UA
	 *
	 * @param string $name
	 * @param string $value
	 * @return void
	 * @author Elmer Zhang
	 * @ignore
	 */
	public function setOpt( $name , $value )
	{
		$name = trim($name);
		$this->opt_[$name] = $value;
	}

	/**
	 * �ڷ����������,�������cookie����
	 *
	 * @param array $cookies Ҫ��ӵ�Cookies����ʽ��array('key1' => 'value1', 'key2' => 'value2', ....)
	 * @return void
	 * @author zhiyong
	 */
	public function setCookies( $cookies = array() )
	{
		if ( is_array($cookies) and !empty($cookies) ) {
			foreach ( $cookies as $k => $v ) {
				$this->setCookie($k, $v);
			}
		}
	}

	/**
	 * �ڷ����������,���cookie����,�˺����ɶ�ε���,��Ӷ��cookie
	 *
	 * @param string $name
	 * @param string $value
	 * @return void
	 * @author zhiyong
	 */
	public function setCookie( $name , $value )
	{
		$name = trim($name);
		array_push($this->cookies_, "$name=$value");
	}

	/**
	 * �Ƿ�����ضϣ�Ĭ��Ϊ������
	 *
	 * �������Ϊtrue,���������ݳ��������Сʱ,�Զ���ȡ���ϴ�С�Ĳ���;<br />
	 * �������Ϊfalse,���������ݳ��������Сʱ,ֱ�ӷ���false;
	 *
	 * @param bool $allow
	 * @return void
	 * @author zhiyong
	 */
	public function setAllowTrunc($allow) {
		$this->opt_["truncated"] = $allow;
	}

	/**
	 * �������ӳ�ʱʱ��,��ʱ�����С��SAEϵͳ���õ�ʱ��,������SAEϵͳ����Ϊ׼��Ĭ��Ϊ5�룩
	 *
	 * @param int $ms ����
	 * @return void
	 * @author zhiyong
	 */
	public function setConnectTimeout($ms) {
		$this->opt_["connecttimeout"] = $ms;
	}

	/**
	 * ���÷��ͳ�ʱʱ��,��ʱ�����С��SAEϵͳ���õ�ʱ��,������SAEϵͳ����Ϊ׼��Ĭ��Ϊ20�룩
	 *
	 * @param int $ms ����
	 * @return void
	 * @author zhiyong
	 */
	public function setSendTimeout($ms) {
		$this->opt_["sendtimeout"] = $ms;
	}

	/**
	 * ���ö�ȡ��ʱʱ��,��ʱ�����С��SAEϵͳ���õ�ʱ��,������SAEϵͳ����Ϊ׼��Ĭ��Ϊ60�룩
	 *
	 * @param int $ms ����
	 * @return void
	 * @author zhiyong
	 */
	public function setReadTimeout($ms) {
		$this->opt_["ReadTimeout"] = $ms;
	}

	/**
	 * ������ҳ����ת��ҳʱ,�Ƿ�������ת,SAE���֧��5����ת(Ĭ�ϲ���ת)
	 *
	 * @param bool $allow �Ƿ�������ת��true:����false:��ֹ��Ĭ��Ϊtrue
	 * @return void
	 * @author zhiyong
	 */
	public function setAllowRedirect($allow = true) {
		$this->opt_["redirect"] = $allow;
	}

	/**
	 * ����HTTP��֤�û�������
	 *
	 * @param string $username HTTP��֤�û���
	 * @param string $password HTTP��֤����
	 * @return void
	 * @author zhiyong
	 */
	public function setHttpAuth($username, $password) {
		$this->opt_["username"] = $username;
		$this->opt_["password"] = $password;
	}

	/**
	 * ��������
	 *
	 * <code>
	 * <?php
	 * echo "Use callback function\n";
	 *
	 * function demo($content) {
	 * 		echo strtoupper($content);
	 * }
	 * 
	 * $furl = new SaeFetchurl();
	 * $furl->fetch($url, $opt, 'demo');
	 * 
	 * echo "Use callback class\n";
	 * 
	 * class Ctx {
	 *  	public function demo($content) {
	 * 				$this->c .= $content;	
	 * 		}
	 * 		public $c;
	 * };
	 * 
	 * $ctx = new Ctx;
	 * $furl = new SaeFetchurl();
	 * $furl->fetch($url, $opt, array($ctx, 'demo'));
	 * echo $ctx->c;
	 * ?>
	 * </code>
	 *
	 * @param string $url
	 * @param array $opt �����������ʽ��array('key1'=>'value1', 'key2'=>'value2', ... )�������б�
	 *  - truncated		����		�Ƿ�ض�
	 *  - redirect			����		�Ƿ�֧���ض���
	 *  - username			�ַ���		http��֤�û���
	 *  - password			�ַ���		http��֤����
	 *  - useragent		�ַ���		�Զ���UA
	 * @param callback $callback ���������ص����ݵĺ���������Ϊ��������ĳ��ʵ������ķ�����
	 * @return string �ɹ�ʱ��ȡ�������ݣ����򷵻�false
	 * @author zhiyong
	 */
	public function fetch( $url, $opt = NULL, $callback=NULL )
	{
		if (count($this->cookies_) != 0) {
			$this->opt_["cookie"] = join("; ", $this->cookies_);
		}
		$opt = ($opt) ?  array_merge($this->opt_, $opt) : $this->opt_;
		return $this->impl_->fetch($url, $opt, $this->headers_, $callback);
	}

	/**
	 * �������ݵ�header��Ϣ
	 *
	 * @param bool $parse �Ƿ����header��Ĭ��Ϊtrue��
	 * @return array
	 * @author zhiyong
	 */
	public function responseHeaders($parse = true)
	{
		$items = explode("\r\n", $this->impl_->headerContent());
		if (!$parse) {
			return $items;
		}
		array_shift($items);
		$headers = array();
		foreach ($items as $_) {
			$pos = strpos($_, ":");
			$key = trim(substr($_, 0, $pos));
			$value = trim(substr($_, $pos + 1));
			if ($key == "Set-Cookie") {
				if (array_key_exists($key, $headers)) {
					array_push($headers[$key], trim($value));
				} else {
					$headers[$key] = array(trim($value));
				}
			} else {
				$headers[$key] = trim($value);
			}
		}
		return $headers;
	}

	/**
	 * ����HTTP״̬��
	 *
	 * @return int
	 * @author Elmer Zhang
	 */
	public function httpCode() {
		return $this->impl_->httpCode();
	}

	/**
	 * ������ҳ����
	 * ������fetch()��������falseʱ
	 *
	 * @return string
	 * @author Elmer Zhang
	 */
	public function body() {
		return $this->impl_->body();
	}

	/**
	 * ����ͷ��ߵ�cookie��Ϣ
	 * 
	 * @param bool $all �Ƿ񷵻�����Cookies��Ϣ��Ϊtrueʱ������Cookie��name,value,path,max-age��Ϊfalseʱ��ֻ����Cookies��name, value
	 * @return array
	 * @author zhiyong
	 */
	public function responseCookies($all = true)
	{
		$header = $this->impl_->headerContent();
		$matchs = array();
		$cookies = array();
		$kvs = array();
		if (preg_match_all('/Set-Cookie:\s([^\r\n]+)/i', $header, $matchs)) {
			foreach ($matchs[1] as $match) {
				$cookie = array();
				$items = explode(";", $match);
				foreach ($items as $_) {
					$item = explode("=", trim($_));
					$cookie[$item[0]]= $item[1];
				}
				array_push($cookies, $cookie);
				$kvs = array_merge($kvs, $cookie);
			}
		}
		if ($all) {
			return $cookies;
		} else {
			unset($kvs['path']);
			unset($kvs['max-age']);
			return $kvs;
		}
	}

	/**
	 * ���ش�����
	 *
	 * @return int
	 * @author zhiyong
	 */
	public function errno()
	{
		if ($this->impl_->errno() != 0) {
			return $this->impl_->errno();
		} else {
			if ($this->impl_->httpCode() != 200) {
				return $this->impl_->httpCode();
			}
		}
		return 0;
	}

	/**
	 * ���ش�����Ϣ
	 *
	 * @return string
	 * @author zhiyong
	 */
	public function errmsg()
	{
		if ($this->impl_->errno() != 0) {
			return $this->impl_->error();
		} else {
			if ($this->impl_->httpCode() != 200) {
				return $this->impl_->httpDesc();
			}
		}
		return "";
	}

	/**
	 * ��������������³�ʼ��,���ڶ������һ��SaeFetchurl����
	 *
	 * @return void
	 * @author Elmer Zhang
	 */
	public function clean() {
		$this->__construct();
	}

	/**
	 * ����/�رյ���ģʽ
	 *
	 * @param bool $on true���������ԣ�false���رյ���
	 * @return void
	 * @author Elmer Zhang
	 */
	public function debug($on) {
		if ($on) {
			$this->impl_->setDebugOn();
		} else {
			$this->impl_->setDebugOff();
		}
	}


	private $impl_;
	private $opt_;
	private $headers_;

}


/**
 * FetchUrl , the sub class of SaeFetchurl
 *
 *
 * @package sae
 * @subpackage fetchurl
 * @author  zhiyong
 * @ignore
 */
class FetchUrl {
	const end_         = "http://fetchurl.sae.sina.com.cn/" ;
	const maxRedirect_ = 5;
	public static $disabledHeaders = array(
		'content-length',
		'host',
		'vary',
		'via',
		'x-forwarded-for',
		'fetchurl',
		'accesskey',
		'timestamp',
		'signature',
		'allowtruncated',
		'connecttimeout',
		'sendtimeout',
		'readtimeout',
	);

	public function __construct($accesskey, $secretkey) {
		$accesskey = trim($accesskey);
		$secretkey = trim($secretkey);

		$this->accesskey_ = $accesskey;
		$this->secretkey_ = $secretkey;

		$this->errno_ = 0;
		$this->error_ = null;
		$this->debug_ = false;
	}

	public function __destruct() {
		// do nothing
	}

	public function setAccesskey($accesskey) {
		$accesskey = trim($accesskey);
		$this->accesskey_ = $accesskey;
	}

	public function setSecretkey($secretkey) {
		$secretkey = trim($secretkey);
		$this->secretkey_ = $secretkey;
	}

	public function setDebugOn() {
		$this->debug_ = true;
	}

	public function setDebugOff() {
		$this->debug_ = false;
	}

	public function fetch($url, $opt = null, $headers = null, $callback = null) {

		$url = trim($url);
		if (substr($url, 0, 7) != 'http://' && substr($url, 0, 8) != 'https://') {
			$url = 'http://' . $url;
		}

		$this->callback_ = $callback;

		$maxRedirect = FetchUrl::maxRedirect_;
		if (is_array($opt) && array_key_exists('redirect',$opt) && !$opt['redirect']) {
			$maxRedirect = 1;
		}

		for ($i = 0; $i < $maxRedirect; ++$i) {
			$this->dofetch($url, $opt, $headers);
			if ($this->errno_ == 0) {
				if ($this->httpCode_ == 301 || $this->httpCode_ == 302) {
					$matchs = array();
					if (preg_match('/Location:\s([^\r\n]+)/i', $this->header_, $matchs)) {
						$newUrl = $matchs[1];
						// if new domain
						if (strncasecmp($newUrl, "http://", strlen("http://")) == 0) {
							$url = $newUrl;
						} else {
							$url = preg_replace('/^((?:https?:\/\/)?[^\/]+)\/(.*)$/i', '$1', $url) . "/". $newUrl;
						}

						if ($this->debug_) {
							echo "[debug] redirect to $url\n";
						}
						continue;
					}
				}
			}
			break;
		}

		if ($this->errno_ == 0 && $this->httpCode_ == 200) {
			return $this->body_;
		} else {
			return false;
		}
	}

	public function headerContent() {
		return $this->header_;
	}

	public function errno() {
		return $this->errno_;
	}

	public function error() {
		return $this->error_;
	}

	public function httpCode() {
		return $this->httpCode_;
	}

	public function body() {
		return $this->body_;
	}

	public function httpDesc() {
		return $this->httpDesc_;
	}

	private function signature($url, $timestamp) {
		$content = "FetchUrl"  . $url .
			"TimeStamp" . $timestamp .
			"AccessKey" . $this->accesskey_;
		$signature = (base64_encode(hash_hmac('sha256',$content,$this->secretkey_,true)));
		if ($this->debug_) {
			echo "[debug] content: $content" . "\n";
			echo "[debug] signature: $signature" . "\n";
		}
		return $signature;
	}

	// we have to set wirteBody & writeHeader public
	// for we used them in curl_setopt()
	public function writeBody($ch, $body) {
		if ($this->callback_) {
			call_user_func($this->callback_, $body);
		} else {
			$this->body_ .= $body;	
		}
		if ($this->debug_) {
			echo "[debug] body => $body";
		}
		return strlen($body);
	}

	public function writeHeader($ch, $header) {
		$this->header_ .= $header;
		if ($this->debug_) {
			echo "[debug] header => $header";	
		}
		return strlen($header);	
	}

	private function dofetch($url, $opt, $headers_) {


		$this->header_ = $this->body_ = null;
		$headers = array();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false) ;
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,true) ;
		curl_setopt($ch, CURLOPT_WRITEFUNCTION, array($this, 'writeBody'));
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'writeHeader'));
		if ($this->debug_) {
			curl_setopt($ch, CURLOPT_VERBOSE, true);
		}

		if (is_array($opt) && !empty($opt)) {
			foreach( $opt as $k => $v) {
				switch(strtolower($k)) {
				case 'username':
					if (array_key_exists("password",$opt)) {
						curl_setopt($ch, CURLOPT_USERPWD, $v . ":" . $opt["password"]);
					}
					break;
				case 'password':
					if (array_key_exists("username",$opt)) {
						curl_setopt($ch, CURLOPT_USERPWD, $opt["username"] . ":" . $v);
					}
					break;
				case 'useragent':
					curl_setopt($ch, CURLOPT_USERAGENT, $v);
					break;
				case 'post':
					curl_setopt($ch, CURLOPT_POSTFIELDS, $v);
					break;
				case 'cookie':
					curl_setopt($ch, CURLOPT_COOKIESESSION, true);
					curl_setopt($ch, CURLOPT_COOKIE, $v);
					break;
				case 'multipart':
					if ($v) array_push($headers, "Content-Type: multipart/form-data");
					break;
				case 'truncated':
					array_push($headers, "AllowTruncated:" . $v);
					break;
				case 'connecttimeout':
					array_push($headers, "ConnectTimeout:" . intval($v));
					break;
				case 'sendtimeout':
					array_push($headers, "SendTimeout:" . intval($v));
					break;
				case 'readtimeout':
					array_push($headers, "ReadTimeout:" . intval($v));
					break;
				default:
					break;

				}
			}
		}

		if (isset($opt['method'])) {
			if (strtolower($opt['method']) == 'get') {
				curl_setopt($ch, CURLOPT_HTTPGET, true);
			}
		}

		if (is_array($headers_) && !empty($headers_)) {
			foreach($headers_ as $k => $v) {
				if (!in_array(strtolower($k), FetchUrl::$disabledHeaders)) {
					array_push($headers, "{$k}:" . $v);
				}
			}
		}

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_ENCODING, "");
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);

		curl_exec($ch);
		$info = curl_getinfo($ch);
		if ($this->debug_) {
			echo "[debug] curl_getinfo => " . print_r($info, true) . "\n";
		}
		$this->errno_ = curl_errno($ch);
		$this->error_ = curl_error($ch);

		if ($this->errno_ == 0) {
			$matchs = array();
			if (preg_match('/^(?:[^\s]+)\s([^\s]+)\s([^\r\n]+)/', $this->header_, $matchs)) {
				$this->httpCode_ = $matchs[1];
				$this->httpDesc_ = $matchs[2];
				if ($this->debug_) {
					echo "[debug] httpCode = " . $this->httpCode_ . "  httpDesc = " . $this->httpDesc_ . "\n";
				}
			} else {
				$this->errno_ = -1;
				$this->error_ = "invalid response";
			}
		}
		curl_close($ch);
	}

	private $accesskey_;
	private $secretkey_;

	private $errno_;
	private $error_;

	private $httpCode_;
	private $httpDesc_;
	private $header_;
	private $body_;

	private $debug_;

}