<?php
namespace Vikas\Plugin;

use Vikas\Facebook\Api as Facebook;
use Vikas\Facebook\ApiException as FacebookApiException;
use Vikas\Plugin\FacebookChat;

class FacebookAuth {

    const DEFAULT_USER = "me";

    protected $appId;
    protected $secret;
    protected $callback;
    protected $facebook;
    protected $user_id;
    protected $username;
    protected $access_token;
    protected $user;
    protected $user_friends;
    protected $scope;

    public function __construct ($app_id, $secret, $scope = null, $callback = null) {
        if (!$app_id) {
            throw new FacebookApiException("API KEY is not valid");
        }
        $this->appId = $app_id;
        if (!$secret) {
            throw new FacebookApiException("API SECRET is not valid");
        }
        $this->secret = $secret;

        $this->scope = $scope;
        if ($callback) {
            $this->callback = $callback;
        }
        $this->facebook = new Facebook(array(
            'appId' => $this->appId,
            'secret' => $this->secret
        ));
        return $this;
    }

    public function validateAuth () {
        if ($this->facebook == null) {
            return false;
        }

        $user = $this->facebook->getUser();
        if ($user) {
            $this->user = $this->facebook->api('/me');
            $this->user_id = $this->user['id'];
            $this->username = $this->user['username'];
            $this->access_token = $this->facebook->getAccessToken();
            return $this->user;
        } else {
            return false;
        }
    }

    public function getLoginUrl ($mobile = false) {
        if ($this->facebook == null) {
            return false;
        }
        if (!$this->facebook->getUser()) {
            $login_params = array();
            $login_params['scope'] = ($this->scope != null) ? $this->scope : "id,name,about,birthday,email,gender,hometown,last_name,address";
            if ($this->callback) {
                $login_params['redirect_uri'] = $this->callback;
            }
            return $this->facebook->getLoginUrl($login_params, $mobile);
        }
    }

    public function getAccessToken () {
        if ($this->facebook == null) {
            return false;
        }
        return $this->facebook->getAccessToken();
    }

    public function getFriends ($query = false) {
        if ($this->facebook == null) {
            return false;
        }

        $friends_data = array();
        $fileds = "name,username,birthday,first_name,last_name,hometown,location,picture.type(large)";
        $access_token = $this->facebook->getAccessToken();
        if (!$query) {
            $query = "fields={$fileds}";
        }
        $friends = $this->facebook->api("/me/friends?{$query}", array('access_token' => $access_token));

        if (sizeof($friends['data']) > 0) {
            $friends_data = array_merge($friends_data, $friends['data']);
        }
        if (isset($friends['paging']['next']) && sizeof($friends['data']) > 0) {
            $uri_query = parse_url($friends['paging']['next']);
            $query = $uri_query['query'];
            $friends_data = array_merge($friends_data, $this->getFriends($query));
        }

        $this->user_friends = $friends_data;
        return $this->user_friends;
    }

    public function sendMailMessage ($to, $message, $sender = null) {
        if ($this->facebook == null) {
            return false;
        }
        if ($sender) {
            $from = $sender;
        } else {
            $from = $this->username;
        }
        $from = $from . '@chat.facebook.com';
        $to = $to . '@chat.facebook.com';
        $headers = 'MIME-Version: 1.0' . '\r\n';
        $headers .= 'Content-Type: text/html; charset="UTF-8";' . '\r\n';
        $headers .= 'Content-Transfer-Encoding: 7bit' . '\r\n';
        $headers .= 'Date: ' . date('r', $_SERVER['REQUEST_TIME']) . '\r\n';
        $headers .= 'Message-ID: <' . $_SERVER['REQUEST_TIME'] . md5($_SERVER['REQUEST_TIME']) . '@' . $_SERVER['SERVER_NAME'] . '>' . '\r\n';
        $headers .= 'From: ' . $from . '\r\n';
        $headers .= 'Reply-To: ' . $from . '\r\n';
        $headers .= 'Return-Path: ' . $from . '\r\n';
        $headers .= 'X-Mailer: PHP v' . phpversion() . '\r\n';
        $headers .= 'X-Originating-IP: ' . $_SERVER['SERVER_ADDR'] . '\r\n';

        $subject = "Test Email From PHP SDK";
        return mail($to, $subject, $message, $headers);
    }

    public function sendPrivateMessage ($from, $to, $message) {
        if ($this->facebook == null) {
            return false;
        }

        /**
         * Message Body
         */
        $message_params = array(
            'access_token' => $this->facebook->getAccessToken(),
            'message' => $message,
            'created_time' => time(),
            'from' => array(
                "id" => $from['id'],
                "name" => $from['name']
            ),
            'to' => array(
                "id" => $to['id'],
                "name" => $to['name']
            ),
        );
        $uri = "/me/feed";
        try {
            $this->facebook->api($uri, 'POST', $message_params);
        } catch (FacebookApiException $e) {
            echo $e->getMessage();
        }
    }

    public function postOnWall ($message, $user_id = false) {
        if ($this->facebook == null) {
            return false;
        }
        /**
         * Message Body
         */
        $message_params = array(
            'access_token' => $this->facebook->getAccessToken(),
            'message' => $message,
            'name' => 'IndiaNIC Infotech Limited',
            'caption' => "IndiaNIC Infotech Limited",
            'link' => 'http://www.indianic.com',
            'description' => 'A Complete Mobile Solutions Provider (We fuel the fire of success by competing ourselves and try to become better than earlier).',
            'picture' => 'http://www.indianic.com/newsite_v5/wp-content/themes/indianic/images/old_logo.png',
            'actions' => array(
                array(
                    'access_token' => $this->facebook->getAccessToken(),
                    'name' => 'IndiaNIC',
                    'link' => 'http://www.indianic.com'
                )
            )
        );

        try {
            $this->facebook->api('/me/feed', 'POST', $message_params);
        } catch (FacebookApiException $e) {
            echo $e->getMessage();
        }
    }

    public function postOnFriendsWall ($friend_id, $message) {
        if ($this->facebook == null) {
            return false;
        }
        /**
         * Message Body
         */
        $message_params = array(
            'access_token' => $this->facebook->getAccessToken(),
            'message' => $message,
            'name' => 'IndiaNIC Infotech Limited',
            'caption' => "IndiaNIC Infotech Limited",
            'link' => 'http://www.indianic.com',
            'description' => 'A Complete Mobile Solutions Provider (We fuel the fire of success by competing ourselves and try to become better than earlier).',
            'picture' => 'http://www.indianic.com/newsite_v5/wp-content/themes/indianic/images/old_logo.png',
            'actions' => array(
                array(
                    'access_token' => $this->facebook->getAccessToken(),
                    'name' => 'IndiaNIC',
                    'link' => 'http://www.indianic.com'
                )
            )
        );
        try {

            $this->facebook->api("/$friend_id/feed", 'POST', $message_params);
        } catch (FacebookApiException $e) {
            echo $e->getMessage();
        }
    }

    public function sendSms ($user_id, $message) {
        if ($this->facebook == null) {
            return false;
        }
        try {
            $res = $this->facebook->api('/sms.send/', 'GET', array('uid' => $user_id, 'message' => $message));
            var_dump($res);
        } catch (FacebookApiException $e) {
            echo $e->getMessage();
        }
    }

    public function fbChat ($to, $message) {
        return new FacebookChat($this->user_id, $to, $message, $this->appId, $this->access_token);
    }

    public function getProfileImage ($urlOnly = false, $user_id = false) {
        if ($this->facebook == null) {
            return false;
        }
        if ($user_id) {
            $uri = $user_id;
        } else {
            $uri = self::DEFAULT_USER;
        }
        $user_image_url = false;
        try {
            $picutre = $this->facebook->api("/{$uri}?fields=picture.type(large)");
            if ($urlOnly) {
                if (isset($picutre['picture'])) {
                    $user_image = $picutre['picture'];
                    if (isset($user_image['data'])) {
                        $user_image_url = isset($user_image['data']['url']) ? $user_image['data']['url'] : false;
                    }
                }
            } else {
                $user_image_url = $picutre;
            }
        } catch (FacebookApiException $e) {
            echo $e->getMessage();
        }

        return $user_image_url;
    }

    public function getUserFollows ($user_id = false) {
        if ($this->facebook == null) {
            return false;
        }
        if ($user_id) {
            $uri = $user_id;
        } else {
            $uri = self::DEFAULT_USER;
        }

        try {
            return $this->facebook->api("/{$uri}/follows");
        } catch (FacebookApiException $e) {
            echo $e->getMessage();
        }

        return false;
    }

    public function getUserLikes ($user_id = false, $query = false) {
        if ($this->facebook == null) {
            return false;
        }
        if ($user_id) {
            $uri = $user_id;
        } else {
            $uri = self::DEFAULT_USER;
        }


        $like_data = array();
        $fileds = "name,id";
        $access_token = $this->facebook->getAccessToken();
        if (!$query) {
            $query = "fields={$fileds}";
        }
        $likes = $this->facebook->api("/{$uri}/likes?{$query}", array('access_token' => $access_token));

        if (sizeof($likes['data']) > 0) {
            $like_data = array_merge($like_data, $likes['data']);
        }
        if (isset($likes['paging']['next']) && sizeof($likes['data']) > 0) {
            $uri_query = parse_url($likes['paging']['next']);
            $query = $uri_query['query'];
            $like_data = array_merge($like_data, $this->getFriends($query));
        }
        $total_likes = count($like_data);
        return array("total_like" => $total_likes, "data" => $like_data);
    }

    /**
     * ----------------------------FQL----------------------------------------------
     */
    public function countLikes ($user_id) {
        if ($this->facebook == null) {
            return false;
        }
        if (empty($user_id)) {
            return false;
        }
        $params = array(
            'method' => 'fql.query',
            'query' => "SELECT object_id, object_type FROM like WHERE user_id = {$user_id} LIMIT 10000000",
            'callback' => '');

        $likes = $this->facebook->api($params);
        if ($likes) {
            return $likes;
        }
        return false;
    }

    public function getStatusCount () {
        #SELECT message, post_id, description FROM stream WHERE source_id = 641481178 AND type = 46 LIMIT 10000
        /**
         * 11 - Group created
         * 12 - Event created
         * 46 - Status update
         * 56 - Post on wall from another user
         * 66 - Note created
         * 80 - Link posted
         * 128 - Video posted
         * 247 - Photos posted
         * 237 - App story
         * 257 - Comment created
         * 272 - App story
         * 285 - Checkin to a place
         * 308 - Post in Group
         */
    }

}

?>
