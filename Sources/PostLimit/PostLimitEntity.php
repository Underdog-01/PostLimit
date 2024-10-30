<?php

declare(strict_types=1);

/**
 * Post Limit mod (SMF)
 *
 * @package PostLimit
 * @version 1.1
 * @author Michel Mendiola <suki@missallsunday.com>
 * @copyright Copyright (c) 2024  Michel Mendiola
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace PostLimit;

class PostLimitEntity
{
    protected int $idUser;
    protected string $idBoards;
    protected int $postLimit;
    protected int $postCount;

    public const ID_USER = 'id_user';
    public const ID_BOARDS = 'id_boards';
    public const POST_LIMIT = 'post_limit';
    public const POST_COUNT = 'post_count';
    public const COLUMNS = [
        self::ID_USER => 'int',
        self::ID_BOARDS => 'string',
        self::POST_LIMIT => 'int',
        self::POST_COUNT => 'int',
    ];
    public const TABLE = 'post_limit';

    public function __construct(array $entry = [])
    {
        $this->setEntry($entry);
    }

    public function setEntry(array $entry): void
    {
        foreach ($entry as $key => $value) {
            $setCall = 'set' . $this->snakeToCamel($key);
            $this->{$setCall}($value);
        }
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
    public function getIdUser(): int
    {
        return $this->idUser;
    }

    public function setIdUser(int $idUser): void
    {
        $this->idUser = $idUser;
    }

    public function getIdBoards(): array
    {
        return explode(',', $this->idBoards);
    }

    public function setIdBoards(array $idBoards): void
    {
        $this->idBoards = implode(',', $idBoards);
    }

    public function getPostLimit(): int
    {
        return $this->postLimit;
    }

    public function setPostLimit(int $postLimit): void
    {
        $this->postLimit = $postLimit;
    }

    public function getPostCount(): int
    {
        return $this->postCount;
    }

    public function setPostCount(int $postCount): void
    {
        $this->postCount = $postCount;
    }

    public function snakeToCamel(string $input): string
    {
        return \lcfirst(\str_replace('_', '', \ucwords($input, '_')));
    }
}
