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
        $payload = file_get_contents('php://input');
        $signature = isset($_SERVER['HTTP_X_HUB_SIGNATURE']) ? $_SERVER['HTTP_X_HUB_SIGNATURE'] : '';

        // signature must be valid
        if (!$this->validateSignature($signature, $payload)) {
            return false;
        }
        
        // we must have those element from github in headers
        if (empty($_SERVER['HTTP_X_GITHUB_EVENT']) || empty($_SERVER['HTTP_X_GITHUB_DELIVERY'])) {
            return false;
        }

        $this->_data        = json_decode($payload,true);
        $this->_event       = $_SERVER['HTTP_X_GITHUB_EVENT'];
        $this->_delivery    = $_SERVER['HTTP_X_GITHUB_DELIVERY'];
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
