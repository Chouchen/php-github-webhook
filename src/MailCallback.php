<?php

namespace GitHubWebhook;

/**
 * Class PushCallback
 *
 * Just an quick example
 *
 * @package GitHubWebhook
 */
class MailCallback extends CallbackAbstract
{

    public function run($data)
    {
        $message = $data['commits'][0]['author']['name']. ' has commited.'."\n";
        foreach ($data['commits'][0]['added'] as $file) {
            $message .= "Added ".$file."\n";
        }
        foreach ($data['commits'][0]['removed'] as $file) {
            $message .= "Removed ".$file."\n";
        }
        foreach ($data['commits'][0]['modified'] as $file) {
            $message .= "Modified ".$file."\n";
        }
        mail ('your@mail.com', '[Github] new commit', $message);
    }
}