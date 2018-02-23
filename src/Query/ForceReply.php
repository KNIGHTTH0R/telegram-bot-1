<?php

namespace alexshadie\TelegramBot\Query;

use alexshadie\TelegramBot\Objects\Object;

/**
 * Class ForceReply
 * Upon receiving a message with this object, Telegram clients will display a reply interface to the user
 * (act as if the user has selected the bot‘s message and tapped ’Reply').
 * This can be extremely useful if you want to create user-friendly step-by-step
 * interfaces without having to sacrifice privacy mode.
 * @package telegram
 */
class ForceReply extends Object
{
    /**
     * @var boolean
     * Shows reply interface to the user, as if they manually selected the bot‘s message and tapped ’Reply'
     */
    private $force_reply;
    /**
     * @var boolean|null
     * Опционально. Use this parameter if you want to force reply from specific users only.
     * Targets:
     * 1) users that are @mentioned in the text of the Message object;
     * 2) if the bot's message is a reply (has reply_to_message_id), sender of the original message.
     */
    private $selective;
}