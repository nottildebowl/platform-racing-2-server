<?php

class ChatRoom extends Room
{

    private $keep_count = 18;
    private $chat_array = array();
    protected $room_name = 'chat_room';

    public $chat_room_name;


    public function __construct($chat_room_name)
    {
        $this->chat_room_name = htmlspecialchars($chat_room_name);

        global $chat_room_array;
        $chat_room_array[htmlspecialchars($chat_room_name)] = $this;

        $this->chat_array = array_fill(0, $this->keep_count, '');
    }


    public function clear()
    {
        for ($i = 0; $i <= $this->keep_count; $i++) {
            $this->send_chat('systemChat` ');
        }
    }


    public function add_player($player)
    {
        Room::add_player($player);
        global $guild_id, $player_array;

        $welcome_message = 'systemChat`Welcome to chat room '.$this->chat_room_name.'! ';
        if (count($this->player_array) <= 1) {
            $welcome_message .= 'You\'re the only person here!';
        } else {
            $welcome_message .= 'There are '.count($player_array).' people online, and '.count($this->player_array).' people in this chat room.';
        }
        if ($this->chat_room_name == 'main' && $guild_id == 0) {
            $welcome_message .= ' Before chatting, please read the PR2 rules listed here: https://jiggmin2.com/forums/showthread.php?tid=385';
        }
        if ($this->chat_room_name == 'mod' && $guild_id == 0) {
            $welcome_message .= ' ~Bowl4Mod ' ;
        }
        $player->socket->write($welcome_message);

        foreach ($this->chat_array as $chat_message) {
            if ($chat_message != '' && !$player->is_ignored_id($chat_message->from_id) && isset($player->socket)) {
                $player->socket->write($chat_message->message);
            }
        }
    }


    public function remove_player($player)
    {
        Room::remove_player($player);
        if (count($this->player_array) <= 0 && $this->chat_room_name != "main" && $this->chat_room_name != "mod" && $this->chat_room_name != "admin") {
            $this->remove();
        }
    }


    public function send_chat($message, $user_id = -1)
    {
        $chat_message = new ChatMessage($user_id, $message);

        array_push($this->chat_array, $chat_message);

        $this->chat_array[0] = null;
        array_shift($this->chat_array);

        foreach ($this->player_array as $player) {
            if (!$player->is_ignored_id($user_id)) {
                $player->socket->write($message);
            }
        }
    }


    public function get_record()
    {
        $str = '';
        foreach ($this->chat_array as $chat_message) {
            if ($chat_message != '') {
                $str .= '<br/>'.$chat_message->message;
            }
        }
        return $str;
    }


    public function remove()
    {
        global $chat_room_array;
        $chat_room_array[$this->chat_room_name] = null;
        unset($chat_room_array[$this->chat_room_name]);

        $this->chat_array = null;
        $this->room_name = null;
        $this->chat_room_name = null;

        parent::remove();
    }
}
