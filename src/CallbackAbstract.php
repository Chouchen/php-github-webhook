<?php
namespace GitHubWebhook;


abstract class CallbackAbstract {

    public function __construct()
    {
    }

    /**
     * Run the callback with data given by Github
     *
     * @param string[] $data
     */
    abstract public function run($data);

}