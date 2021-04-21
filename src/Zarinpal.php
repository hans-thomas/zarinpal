<?php

    namespace Zarinpal;

    use Zarinpal\Drivers\DriverInterface;
    use Zarinpal\Drivers\RestDriver;

    class Zarinpal {
        private $redirectUrl = 'https://www.zarinpal.com/pg/StartPay/$authority$';
        private $merchantID;
        private $driver;
        private $authority;

        public function __construct( $merchantID, DriverInterface $driver = null ) {
            if ( is_null( $driver ) ) {
                $driver = new RestDriver();
            }
            $this->merchantID = $merchantID;
            $this->driver     = $driver;
        }

        /**
         * send request for money to zarinpal
         * and redirect if there was no error.
         *
         * @param string $callbackURL
         * @param string $Amount
         * @param string $Description
         * @param string $Email
         * @param string $Mobile
         * @param null   $additionalData
         *
         * @return array|@redirect
         */
        public function request( $callbackURL, $Amount, $Description, $Email = null, $Mobile = null ) {
            $inputs = [
                'merchant_id'  => $this->merchantID,
                'callback_url' => $callbackURL,
                'amount'       => $Amount,
                'description'  => $Description,
            ];
            if ( ! is_null( $Email ) ) {
                $inputs[ 'metadata' ][ 'email' ] = $Email;
            }
            if ( ! is_null( $Mobile ) ) {
                $inputs[ 'metadata' ][ 'mobile' ] = $Mobile;
            }
            $results = $this->driver->request( $inputs );

            if ( empty( $results[ 'authority' ] ) ) {
                $results[ 'authority' ] = null;
            }
            $this->authority = $results[ 'authority' ];

            return $results;
        }

        /**
         * verify that the bill is paid or not
         * by checking authority, amount and status.
         *
         * @param $amount
         * @param $authority
         *
         * @return array
         */
        public function verify( $amount, $authority ) {
            // backward compatibility
            if ( count( func_get_args() ) == 3 ) {
                $amount    = func_get_arg( 1 );
                $authority = func_get_arg( 2 );
            }

            $inputs = [
                'merchant_id' => $this->merchantID,
                'authority'  => $authority,
                'amount'     => $amount,
            ];

            return $this->driver->verify( $inputs );
        }

        public function redirect() {
            header( 'Location: ' . str_replace( '$authority$', $this->authority, $this->redirectUrl ) );
            die;
        }

        /**
         * @return string
         */
        public function redirectUrl() {
            return sprintf( $this->redirectUrl, $this->authority );
        }

        /**
         * @return DriverInterface
         */
        public function getDriver() {
            return $this->driver;
        }

        /**
         * active sandbox mod for test env.
         */
        public function enableSandbox() {
            $this->redirectUrl = 'https://sandbox.zarinpal.com/pg/StartPay/$authority$';
            $this->getDriver()->enableSandbox();
        }

        /**
         * active zarinGate mode.
         */
        public function isZarinGate() {
            $this->redirectUrl = $this->redirectUrl . '/ZarinGate';
        }
    }
