<?php

    namespace Zarinpal\Drivers;

    use GuzzleHttp\Client;
    use GuzzleHttp\Exception\RequestException;

    class RestDriver implements DriverInterface {
        protected $baseUrl = 'https://api.zarinpal.com/pg/v4/payment/';

        /**
         * request driver.
         *
         * @param $inputs
         *
         * @return array
         */
        public function request( $inputs ) {
            $result = $this->restCall( env( 'sandbox', false ) ? 'PaymentRequest.json' : 'request.json', $inputs );

            if ( env( 'sandbox', false ) ) {
                if ( isset( $result[ 'Status' ] ) and isset( $result[ 'Authority' ] ) ) {
                    return [ 'authority' => $result[ 'Authority' ] ];
                } else {
                    return 'Error! check result varable for error details';
                }
            }
            if ( isset( $result[ 'data' ][ 'code' ] ) and $result[ 'data' ][ 'code' ] == 100 ) {
                return [ 'authority' => $result[ 'data' ][ 'authority' ] ];
            } else {
                return [ 'error' => $result[ 'errors' ][ 'code' ] . ' : ' . $result[ 'errors' ][ 'message' ] ];
            }
        }

        /**
         * verify driver.
         *
         * @param $inputs
         *
         * @return array
         */
        public function verify( $inputs ) {
            $result = $this->restCall( env( 'sandbox', false ) ? 'PaymentVerification.json' : 'verify.json', $inputs );
            // sandbox support
            if ( env( 'sandbox', false ) ) {
                if ( isset( $result[ 'Status' ] ) ) {
                    return [
                        'status' => 'success',
                        'ref_id' => $result['RefID'],
                    ];
                }
            }


	        $code = $result[ 'data' ][ 'code' ] ?? false;
	        if ( $code == 100 ) {
		        return [
			        'status' => 'success',
			        'ref_id' => $result[ 'data' ][ 'ref_id' ],
		        ];
	        } elseif ( $code == 101 ) {
		        return [
			        'status' => 'verified_before',
			        'ref_id' => $result[ 'data' ][ 'ref_id' ],
		        ];
            } else {
                return [
                    'status'     => 'error',
                    'error'      => ! empty( $result[ 'data' ][ 'code' ] ) ? $result[ 'data' ][ 'code' ] : null,
                    'error_info' => ! empty( $result[ 'errors' ][ 'code' ] ) ? $result[ 'errors' ][ 'code' ] : null,
                ];
            }
        }

        /**
         * refreshAuthority driver.
         *
         * @param $inputs
         *
         * @return array
         */
        public function refreshAuthority( $inputs ) {
            $result = $this->restCall( 'request.json', $inputs );

            if ( isset( $result[ 'data' ][ 'code' ] ) and $result[ 'data' ][ 'code' ] == 100 ) {
                return [ 'status' => 'success', 'refreshed' => true ];
            } else {
                return [
                    'status' => 'error',
                    'error'  => $result[ 'errors' ][ 'code' ] . ' : ' . $result[ 'errors' ][ 'message' ]
                ];
            }
        }

        /**
         * request rest and return the response.
         *
         * @param $uri
         * @param $data
         *
         * @return mixed
         */
        private function restCall( $uri, $data ) {
            try {
                $client   = new Client( [ 'base_uri' => $this->baseUrl ] );
                $response = $client->request( 'POST', $uri, [
                    'json'    => $data,
                    'headers' => [
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen( json_encode( $data ) )
                    ]
                ] );

                $rawBody = $response->getBody()->getContents();
                $body    = json_decode( $rawBody, true );
            } catch ( RequestException $e ) {
                $response = $e->getResponse();
                $rawBody  = is_null( $response ) ? '{"Status":-98,"message":"http connection error"}' : $response->getBody()
                                                                                                                 ->getContents();
                $body     = json_decode( $rawBody, true );
            }

            if ( ! isset( $result[ 'Status' ] ) ) {
                $result[ 'Status' ] = - 99;
            }

            return $body;
        }

        /**
         * @param mixed $baseUrl
         *
         * @return void
         */
        public function setAddress( $baseUrl ) {
            $this->baseUrl = $baseUrl;
        }

        public function enableSandbox() {
            $this->setAddress( 'https://sandbox.zarinpal.com/pg/rest/WebGate/' );
        }
    }
