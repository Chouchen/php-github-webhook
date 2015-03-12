<?php

namespace GitHubWebhook;

/**
 * Class PushCallback
 *
 * Just an quick example
 *
 * @package GitHubWebhook
 */
class MoveFileCallback extends CallbackAbstract
{

    public function run($data)
    {
        if ($data['ref'] === "refs/heads/master") {
            $fileToMove = array_merge($data['commits'][0]['modified'], $data['commits'][0]['added']);
            $fileToDelete = $data['commits'][0]['removed'];
            if ( !is_dir(dirname(__FILE__) . '/tmp' ) ) {
                mkdir(dirname(__FILE__) . '/tmp' );
            }
            foreach ($fileToMove as $file) {
                file_put_contents(
                    dirname(__FILE__) . '/tmp/'.$file,
                    file_get_contents(
                        'https://raw.githubusercontent.com/'.$data['repository']['full_name'].'/master/'.$file
                    )
                );
            }

            foreach ($fileToDelete as $file) {
                if (file_exists(dirname(__FILE__) . '/tmp/'.$file)) {
                    unlink(dirname(__FILE__) . '/tmp/'.$file);
                }
            }
        }
    }
}