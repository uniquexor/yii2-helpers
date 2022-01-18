<?php
    namespace unique\yii2helpers\components;

    /**
     * Class RequestHelper.
     * Provides an easy way to generate headers for a request with default data.
     * @package app\components
     */
    class RequestHelper {

        /**
         * Host header.
         * @var string
         */
        protected string $host;

        /**
         * All headers
         * @var array
         */
        protected array $headers = [];

        /**
         * @param string $host - Host header
         */
        public function __construct( string $host ) {

            $this->host = $host;
        }

        /**
         * Creates a RequestHelper object with Headers set for HTML content.
         * The same headers as set by Chrome are set.
         * @param string $host - Host header value
         * @return RequestHelper
         */
        public static function createDefaultHtmlRequest( string $host ): RequestHelper {

            return ( new self( $host ) )
                ->setAcceptAsHtml()
                ->setAcceptEncoding()
                ->setAcceptLanguage()
                ->setCacheControl()
                ->setConnection()
                ->setContentType()
                ->setHost()
                ->setSec()
                ->setUpgradeInsecureRequests()
                ->setUserAgent();
        }

        /**
         * Creates a RequestHelper object with Headers set for JSON content.
         * The same headers as set by Chrome are set.
         *
         * @param string $host
         * @return RequestHelper
         */
        public static function createDefaultJsonRequest( string $host ): RequestHelper {

            return self::createDefaultHtmlRequest( $host )
                ->setAcceptAsJson()
                ->setHeader( 'x-requested-with', 'XMLHttpRequest' );
        }

        /**
         * Sets the Accept header
         * @param string $value
         * @return RequestHelper
         */
        public function setAccept( string $value = '*/*' ): RequestHelper {

            $this->headers['Accept'] = $value;
            return $this;
        }

        /**
         * Sets Accept header to the default value that Chrome uses to fetch html.
         * @return RequestHelper
         */
        public function setAcceptAsHtml(): RequestHelper {

            return $this->setAccept( 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9' );
        }

        /**
         * Sets Accept header to accept json content.
         * @return RequestHelper
         */
        public function setAcceptAsJson(): RequestHelper {

            return $this->setAccept( 'application/json, text/plain, */*' );
        }

        /**
         * Sets Accept-Encoding header.
         * By default accepts only gzip and deflate, because br causes problems for curl.
         * @param string $value
         * @return RequestHelper
         */
        public function setAcceptEncoding( string $value = 'gzip, deflate' ): RequestHelper {

            $this->headers['Accept-Encoding'] = $value;
            return $this;
        }

        /**
         * Sets Accept-Language header.
         * By default accepts english language.
         * @param string $value
         * @return RequestHelper
         */
        public function setAcceptLanguage( string $value = 'en-US,en;q=0.9,lt-LT;q=0.8,lt;q=0.7' ): RequestHelper {

            $this->headers['Accept-Language'] = $value;
            return $this;
        }

        /**
         * Sets Connection header.
         * @param string $value
         * @return $this
         */
        public function setConnection( string $value = 'keep-alive' ): RequestHelper {

            $this->headers['Connection'] = $value;
            return $this;
        }

        /**
         * Sets Cache-Control and Pragma headers.
         * @param string RequestHelper
         * @return $this
         */
        public function setCacheControl( string $value = 'no-cache' ): RequestHelper {

            $this->headers['Cache-Control'] = $value;
            $this->headers['Pragma'] = $value;
            return $this;
        }

        /**
         * Sets Content-Type header. By default sets 'application/x-www-form-urlencoded'
         * @param string $value
         * @return RequestHelper
         */
        public function setContentType( string $value = 'application/x-www-form-urlencoded' ): RequestHelper {

            $this->headers['Content-Type'] = $value;
            return $this;
        }

        /**
         * Sets Host and Origin headers from the contructor.
         * @return $this
         */
        public function setHost(): RequestHelper {

            $this->headers['Host'] = $this->host;
            $this->headers['Origin'] = 'https://' . $this->host;
            return $this;
        }

        /**
         * Sets the following headers: sec-ch-ua, sec-ch-ua-mobile, Sec-Fetch-Dest, Sec-Fetch-Mode, Sec-Fetch-Site, Sec-Fetch-User
         * If any of the values are false, they are not set.
         * @param string|false $ua - sec-ch-ua header
         * @param string|false $ua_mobile - sec-ch-ua-mobile header
         * @param string|false $dest - Sec-Fetch-Dest header
         * @param string|false $mode - Sec-Fetch-Mode header
         * @param string|false $site - Sec-Fetch-Site header
         * @param string|false $user - Sec-Fetch-User header
         * @return $this
         */
        public function setSec(
            $ua = '" Not;A Brand";v="99", "Google Chrome";v="97", "Chromium";v="97"',
            $ua_mobile = '?0',
            $dest = 'document',
            $mode = 'navigate',
            $site = 'same-origin',
            $user = '?1'
        ): RequestHelper {

            $headers = [
                'sec-ch-ua' => $ua,
                'sec-ch-ua-mobile' => $ua_mobile,
                'Sec-Fetch-Dest' => $dest,
                'Sec-Fetch-Mode' => $mode,
                'Sec-Fetch-Site' => $site,
                'Sec-Fetch-User' => $user,
            ];

            $headers = array_filter( $headers );
            $this->headers = array_merge( $this->headers, $headers );

            return $this;
        }

        /**
         * Sets Upgrade-Insecure-Requests header.
         * @param string $value
         * @return $this
         */
        public function setUpgradeInsecureRequests( string $value = '1' ): RequestHelper {

            $this->headers['Upgrade-Insecure-Requests'] = $value;
            return $this;
        }

        /**
         * Sets User-Agent header.
         * @param string $value
         * @return $this
         */
        public function setUserAgent(
            string $value = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36'
        ): RequestHelper {

            $this->headers['User-Agent'] = $value;
            return $this;
        }

        /**
         * Sets Referer header.
         * @param string $value
         * @return $this
         */
        public function setReferer( string $value ): RequestHelper {

            $this->headers['Referer'] = $value;
            return $this;
        }

        /**
         * Appends given cookies to the Cookie header.
         * @param string $value - Cookies in the form of: [name]=[value];[name]=[value];...
         * @return $this
         */
        public function addCookie( string $value ): RequestHelper {

            if ( ( $this->headers['Cookie'] ?? null ) ) {

                $this->headers['Cookie'] = '';
            } else {

                $this->headers['Cookie'] = '; ';
            }

            $this->headers['Cookie'] .= $value;
            return $this;
        }

        /**
         * Sets a custom header value.
         * @param string $header
         * @param string $value
         * @return $this
         */
        public function setHeader( string $header, string $value ): RequestHelper {

            $this->headers[ $header ] = $value;
            return $this;
        }

        /**
         * Unsets provided header name.
         * @param string $header
         * @return $this
         */
        public function unsetHeader( string $header ): RequestHelper {

            unset( $this->headers[ $header ] );
            return $this;
        }

        /**
         * Returns all headers as name => value pairs.
         * @return array
         */
        public function toArray(): array {

            return $this->headers;
        }

        /**
         * Returns an array of name => value pairs for the Cookie header.
         * Expects cookies in the form of: [name]=[value];[name]=[value];...
         * @return array
         * @throws \Exception
         */
        public function getCookieValues(): array {

            $cookies_array = [];

            $cookies = explode( ';', $this->headers['Cookie'] ?? '' );
            foreach ( $cookies as $cookie ) {

                $cookie = trim( $cookie );
                if ( $cookie ) {

                    $cookie = explode( '=', $cookie );
                    if ( count( $cookie ) >= 2 ) {

                        $name = trim( array_shift( $cookie ) );
                        $cookies_array[ $name ] = implode( '=', $cookie );
                    } else {

                        throw new \Exception( 'Bad cookie format: `' . implode( '=', $cookie ) . '`' );
                    }
                }
            }

            return $cookies_array;
        }
    }