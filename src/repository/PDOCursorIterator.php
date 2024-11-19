<?php

namespace Flender\PhpRouter\Repository;

use Iterator;
use JsonSerializable;
use PDOStatement;

class PDOCursorIterator implements Iterator, JsonSerializable {
    private $pdoStatement;
    private $current;
    private $key;
    private $className;

    public function __construct(PDOStatement $statement, string $className) {
        $this->pdoStatement = $statement;
        $this->className = $className;
        $this->key = 0;
    }

    public function rewind(): void {
        $this->current = $this->pdoStatement->fetchObject($this->className);
        $this->key = 0;
    }

    public function current() {
        return $this->current;
    }

    public function key(): int {
        return $this->key;
    }

    public function next(): void {
        $this->current = $this->pdoStatement->fetchObject($this->className);
        if ($this->current) {
            $this->key++;
        }
    }

    public function valid(): bool {
        return $this->current !== false;
    }

    public function jsonSerialize() {
        return iterator_to_array($this);
    }
}
