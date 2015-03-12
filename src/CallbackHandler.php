<?php
namespace GitHubWebhook;

/**
 * Class CallbackHandler
 * @package GitHubWebhook
 */
class CallbackHandler {

    /**
     * @var array possible github event
     *
     * @see https://developer.github.com/webhooks/#events
     */
    public static $events = [
        '*',
        'commit_comment',
        'create',
        'delete',
        'deployment',
        'deployment_status',
        'fork',
        'gollum',
        'issue_comment',
        'issues',
        'member',
        'membership',
        'page_build',
        'public',
        'pull_request_review_comment',
        'pull_request',
        'push',
        'repository',
        'release',
        'status',
        'team_add',
        'watch'
    ];

    /**
     * @var CallbackAbstract[]
     */
    protected $_callbacks = [];

    /**
     * Constructor
     *
     * @param array $callbacks
     */
    public function __construct(array $callbacks = [])
    {
        $realCallbacks = [];
        if (isset($callbacks) && count($callbacks) > 0) {
            $realCallbacks = $callbacks;
        } else if (is_readable('webhook.ini')) {
            $realCallbacks = parse_ini_file('webhook.ini');
        }
        $this->_parseCallbacks(null, $realCallbacks);
    }

    protected function _parseCallbacks($mainEvent, array $cb)
    {
        foreach ($cb as $event => $callback) {
            if (is_array($callback)) {
                $this->_parseCallbacks($event, $callback);
            } else {
                $clazz = __NAMESPACE__ . '\\' . $callback;
                $this->on($mainEvent, new $clazz);
            }
        }
    }

    /**
     * Check if the given callback exists
     *
     * @param $callback
     *
     * @return bool
     */
    public function hasCallback($callback)
    {
        return array_key_exists($callback, $this->_callbacks);
    }

    /**
     * If the given event exists in the callback list, it will run.
     *
     * @param string $event event type
     * @param $data
     */
    public function emit($event, $data)
    {
        if (isset($this->_callbacks[$event]) && in_array($event, self::$events)) {
            foreach ($this->_callbacks[$event] as $cb) {
                $cb->run($data);
            }
        }
    }

    /**
     * Register a callback to an event
     *
     * @param string           $event
     * @param CallbackAbstract $cb
     */
    public function on($event, CallbackAbstract $cb)
    {
        if (in_array($event, self::$events)) {
            if (!isset($this->_callbacks[$event])) {
                $this->_callbacks[$event] = [];
            }
            $this->_callbacks[$event][] = $cb;
        }
    }

}