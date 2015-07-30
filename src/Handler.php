<?php
namespace GitHubWebhook;

class Handler
{
    /**
     * @var string secret passphrase
     */
    private $_secret;
    /**
     * @var string[] response data
     */
    private $_data;
    /**
     * @var string event type that triggered the web hook
     */
    private $_event;
    /**
     * @var string Unique ID for this delivery
     */
    private $_delivery;
    /**
     * @var CallbackHandler class that handle callback (obviously)
     */
    private $_callbacks;

    /**
     * Constructor
     *
     * @param $secret
     */
    public function __construct($secret)
    {
        $this->_secret = $secret;
        $this->_callbacks = new CallbackHandler();
    }

    ////// GETTER /////
    /**
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @return string
     */
    public function getDelivery()
    {
        return $this->_delivery;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->_event;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->_secret;
    }

    /**
     * If request is valid, then we launch our callback
     *
     * @see #validate()
     * @return bool
     */
    public function handle()
    {
        if (!$this->validate()) {
            return false;
        }

        if ($this->_callbacks->hasCallback($this->_event)) {
            $this->_callbacks->emit($this->_event, $this->_data);
        }
        //exec("git --work-tree={$this->gitDir} pull -f {$this->remote}", $this->gitOutput);
        return true;
    }

    /**
     * Validate the request to github and populate attributes with response
     *
     * @return bool
     */
    public function validate()
    {
        $headers = apache_request_headers();
        $payload = file_get_contents('php://input');

        $signature = isset($headers['X-Hub-Signature']) ? $headers['X-Hub-Signature'] : $headers['X-HUB-SIGNATURE'];

        if (!$this->validateSignature($signature, $payload)) {
            return false;
        }

        $this->_data        = json_decode($payload,true);
        $this->_event       = isset($headers['X-GitHub-Event']) ? $headers['X-GitHub-Event'] : $headers['X-GITHUB-EVENT'];
        $this->_delivery    = isset($headers['X-GitHub-Delivery']) ? $headers['X-GitHub-Delivery'] : $headers['X-GITHUB-DELIVERY'];
        return true;
    }

    /**
     * Validate github signature
     * see https://developer.github.com/webhooks/securing/
     *
     * @param $gitHubSignatureHeader
     * @param $payload
     *
     * @return bool
     */
    protected function validateSignature($gitHubSignatureHeader, $payload)
    {
        list ($algo, $gitHubSignature) = explode("=", $gitHubSignatureHeader);
        $payloadHash = hash_hmac($algo, $payload, $this->_secret);
        return ($payloadHash == $gitHubSignature);
    }
}
