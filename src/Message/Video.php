<?php

namespace alexshadie\TelegramBot\Message;

use alexshadie\TelegramBot\Objects\Object;
use alexshadie\TelegramBot\Type\PhotoSize;

/**
 * This object represents a video file.
 *
 */
class Video extends Object
{
    /**
     * Unique identifier for this file
     *
     * @var string
     */
    private $file_id;

    /**
     * Video width as defined by sender
     *
     * @var int
     */
    private $width;

    /**
     * Video height as defined by sender
     *
     * @var int
     */
    private $height;

    /**
     * Duration of the video in seconds as defined by sender
     *
     * @var int
     */
    private $duration;

    /**
     * Video thumbnail
     *
     * @var PhotoSize|null
     */
    private $thumb;

    /**
     * Mime type of a file as defined by sender
     *
     * @var string|null
     */
    private $mime_type;

    /**
     * File size
     *
     * @var int|null
     */
    private $file_size;

    /**
     * Video constructor.
     *
     * @param string $fileId
     * @param int $width
     * @param int $height
     * @param int $duration
     * @param PhotoSize|null $thumb
     * @param string|null $mimeType
     * @param int|null $fileSize
     */
    public function __construct(string $fileId, int $width, int $height, int $duration, ?PhotoSize $thumb = null, ?string $mimeType = null, ?int $fileSize = null)
    {
        $this->file_id = $fileId;
        $this->width = $width;
        $this->height = $height;
        $this->duration = $duration;
        $this->thumb = $thumb;
        $this->mime_type = $mimeType;
        $this->file_size = $fileSize;
    }

    /**
     * Unique identifier for this file
     *
     * @return string
     */
    public function getFileId(): string
    {
        return $this->file_id;
    }

    /**
     * Video width as defined by sender
     *
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * Video height as defined by sender
     *
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * Duration of the video in seconds as defined by sender
     *
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * Video thumbnail
     *
     * @return PhotoSize|null
     */
    public function getThumb(): ?PhotoSize
    {
        return $this->thumb;
    }

    /**
     * Mime type of a file as defined by sender
     *
     * @return string|null
     */
    public function getMimeType(): ?string
    {
        return $this->mime_type;
    }

    /**
     * File size
     *
     * @return int|null
     */
    public function getFileSize(): ?int
    {
        return $this->file_size;
    }

    /**
      * Creates Video object from data.
      * @param \stdClass $data
      * @return Video
      */
    public static function createFromObject(?\stdClass $data): ?Video
    {
        if (is_null($data)) {
            return null;
        }
        $object = new Video(
            $data->file_id,
            $data->width,
            $data->height,
            $data->duration
        );

        $object->thumb = PhotoSize::createFromObject($data->thumb ?? null);
        $object->mime_type = $data->mime_type ?? null;
        $object->file_size = $data->file_size ?? null;

        return $object;
    }

    /**
      * Creates array of Video objects from data.
      * @param array $data
      * @return Video[]
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
