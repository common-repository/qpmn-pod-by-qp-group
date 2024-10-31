<?php
if (!defined('ABSPATH')) exit;

/**
 * Qpson API client
 */
class Qpson_Client {

    private $key;
    private $lastResponseRaw;
    private $lastResponse;
    private $userAgent = 'Qpson WooCommerce Plugin';
    private $apiUrl;

    private $logger;

    /**
     * @param bool|string $disable_ssl Force HTTP instead of HTTPS for API requests
     *
     * @throws QpsonException if the library failed to initialize
     */
    public function __construct($disable_ssl = false) {

        $this->key = 'Basic ' . Qpson::get_apikey();
        $this->userAgent .= ' ' . QPSON_VERSION . ' (WP ' . get_bloginfo('version') . ' + WC ' . WC()->version . ')';

        if (!function_exists('json_decode') || !function_exists('json_encode')) {
            throw new QpsonException('PHP JSON extension is required for the QPSON API library to work!');
        }

        //setup api host
        $this->apiUrl = QPSON_SERVER;

        if ($disable_ssl) {
            $this->apiUrl = str_replace('https://', 'http://', $this->apiUrl);
        }

        $this->logger = QPSON()->get_logger();

    }

    /**
     * Returns total available item count from the last request if it supports paging (e.g order list) or null otherwise.
     *
     * @return int|null Item count
     */
    public function getItemCount() {
        return isset($this->lastResponse['paging']['total']) ? $this->lastResponse['paging']['total'] : null;
    }


    /**
     * @throws QpsonApiException
     * @throws QpsonException
     */
    public function get($path, $params = array(), $context) {
        return $this->request('GET', $path, $params,null, $context);
    }

    /**
     * @throws QpsonApiException
     * @throws QpsonException
     */
    public function delete($path, $params = array(), $context) {
        return $this->request('DELETE', $path, $params,null, $context);
    }


    /**
     * @throws QpsonApiException
     * @throws QpsonException
     */
    public function post($path, $data = array(), $params = array(), $context) {
        return $this->request('POST', $path, $params, $data, $context);
    }

    /**
     * @throws QpsonApiException
     * @throws QpsonException
     */
    public function put($path, $data = array(), $params = array(), $context) {
        return $this->request('PUT', $path, $params, $data, $context);
    }


    /**
     * @throws QpsonApiException
     * @throws QpsonException
     */
    public function patch($path, $data = array(), $params = array(), $context) {
        return $this->request('PATCH', $path, $params, $data, $context);
    }


    public function getLastResponseRaw() {
        return $this->lastResponseRaw;
    }

    public function getLastResponse() {
        return $this->lastResponse;
    }


    /**
     * @throws QpsonApiException
     * @throws QpsonException
     */
    private function request($method, $path, array $params = array(), $data = null, $context) {

        $this->lastResponseRaw = null;
        $this->lastResponse = null;

        $url = trim($path, '/');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $request = array(
            'timeout' => 120,
            'user-agent' => $this->userAgent,
            'method' => $method,
            'headers' => array(
                'Authorization' => $this->key,
                'Content-Type' => 'application/json'
            ),
            'body' => $data !== null ? json_encode($data) : null,
        );

        $result = wp_remote_get($this->apiUrl . $url, $request);

        if (is_wp_error($result)) {
            $this->log_error($url, $method, $request, $result, $context);
            throw new QpsonException("API request failed - " . $result->get_error_message());
        }

        $this->lastResponseRaw = $result['body'];
        $this->lastResponse = $response = json_decode($result['body'], true);

        if (!isset($response['success'], $response['data'])) {
            $this->log_error($url, $method, $request, $result, $context);
            throw new QpsonException('Invalid API response');
        }
        $is_success = (int)$response['success'];
        if (!$is_success) {
            $this->log_error($url, $method, $request, $result, $context);
            if ($response['data']['status'] == 401) {
                throw new QpsonApiException('store token is invalid');
            }
            throw new QpsonApiException((string)$response['data']['message']);
        }

        return $response['data'];
    }

    public function log_error($url, $method, $request, $result, $context) {
        $this->logger->error(json_encode(array(
                'url' => $this->apiUrl . $url,
                'method' => $method,
                'request' => $request,
                'result' => $result
            )
        ), ['rest_api_request']);
        $this->save_to_wc_log($request, $result, $context?:'qpmn_pod_rest_api_request');
    }

    private function save_to_wc_log( $request, $result, $context ) {

        if ( ! function_exists( 'wc_get_logger' ) ) {
            return false;
        }

        $logger   = wc_get_logger();
        $context  = array( 'source' => $context );
        $log_item = array(
            'request' => (array) $request,
            'results' => (array) $result,
        );
        $logger->error( wc_print_r( $log_item, true ), $context );

        return true;
    }

}


class QpsonException extends Exception {
}

class QpsonApiException extends QpsonException {
}