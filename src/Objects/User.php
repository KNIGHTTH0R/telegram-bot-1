<?php

namespace alexshadie\TelegramBot\Objects;

/**
 * This object represents a Telegram user or bot.
 *
 */
class User extends Object
{
    /**
     * Unique identifier for this user or bot
     *
     * @var int
     */
    private $id;

    /**
     * True, if this user is a bot
     *
     * @var bool
     */
    private $is_bot;

    /**
     * User‘s or bot’s first name
     *
     * @var string
     */
    private $first_name;

    /**
     * User‘s or bot’s last name
     *
     * @var string|null
     */
    private $last_name;

    /**
     * User‘s or bot’s username
     *
     * @var string|null
     */
    private $username;

    /**
     * IETF language tag of the user's language
     *
     * @var string|null
     */
    private $language_code;

    /**
     * User constructor.
     *
     * @param int $id
     * @param bool $isBot
     * @param string $firstName
     * @param string|null $lastName
     * @param string|null $username
     * @param string|null $languageCode
     */
    public function __construct(int $id, bool $isBot, string $firstName, ?string $lastName = null, ?string $username = null, ?string $languageCode = null)
    {
        $this->id = $id;
        $this->is_bot = $isBot;
        $this->first_name = $firstName;
        $this->last_name = $lastName;
        $this->username = $username;
        $this->language_code = $languageCode;
    }

    /**
     * Unique identifier for this user or bot
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * True, if this user is a bot
     *
     * @return bool
     */
    public function getIsBot(): bool
    {
        return $this->is_bot;
    }

    /**
     * User‘s or bot’s first name
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->first_name;
    }

    /**
     * User‘s or bot’s last name
     *
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    /**
     * User‘s or bot’s username
     *
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * IETF language tag of the user's language
     *
     * @return string|null
     */
    public function getLanguageCode(): ?string
    {
        return $this->language_code;
    }

    /**
      * Creates User object from data.
      * @param \stdClass $data
      * @return User
      */
    public static function createFromObject(?\stdClass $data): ?User
    {
        if (is_null($data)) {
            return null;
        }
        $object = new User(
            $data->id,
            $data->is_bot,
            $data->first_name
        );

        $object->last_name = $data->last_name ?? null;
        $object->username = $data->username ?? null;
        $object->language_code = $data->language_code ?? null;

        return $object;
    }

    /**
      * Creates array of User objects from data.
      * @param array $data
      * @return User[]
      */
    public static function createFromObjectList(?array $data): ?array
    {
        if (is_null($data)) {
            return null;
        };
        $objects = [];
        foreach ($data as $row) {
            $objects[] = static::createFromObject($row);
        }
        return $objects;
    }

}
