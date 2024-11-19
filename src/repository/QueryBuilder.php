<?php

namespace Flender\PhpRouter\Repository;
use Iterator;
use mysqli_sql_exception;
use PDO;

class QueryBuilder {

	private string $table_name;
	private ?string $alias;
	private string $select;
	private string $join = '';
    private string $where;
	private string $order;
    private string $group = '';
	private int $offset;
	private int $limit;
	private PDO $pdo;
    private ?string $class_name;
    private array $arguments;

	public function __construct(string $table_name, ?string $alias, PDO $pdo, ?string $class_name = null) {
		$this->table_name = $table_name;
        $this->alias = $alias;
        $this->select = '*';
        $this->where = '';
        $this->order = '';
        $this->offset = 0;
        $this->limit = 0;
		$this->pdo = $pdo;
        $this->arguments = [];
        $this->class_name = $class_name;
	}

    public function select(string $select):QueryBuilder  {
        $this->select = $select;
        return $this;
    }

    public function limit(int $limit):QueryBuilder  {
        $this->limit = $limit;
        return $this;
    }

	public function page(int $page_number, $number_per_page = 10):QueryBuilder  {
		$this->limit = $number_per_page;
		$this->offset = ($page_number-1) * $number_per_page;
		return $this;
	}

	public function join(string $table_name, string $alias, string $relation):QueryBuilder  {
		$this->join .= " INNER JOIN {$table_name} {$alias} ON {$relation}";
		return $this;
	}

    public function leftjoin(string $table_name, string $alias, string $relation):QueryBuilder  {
		$this->join .= " LEFT JOIN {$table_name} {$alias} ON {$relation}";
		return $this;
	}

	public function order(string $order):QueryBuilder  {
		$this->order = $order;
		return $this;
	}

    public function where(string $where):QueryBuilder  {
        $this->where = $where;
        return $this;
    }

    public function group(string $group):QueryBuilder  {
        $this->group = $group;
        return $this;
    }

    public function addArgument(string $key, $value):QueryBuilder  {
        $this->arguments[$key] = $value;
        return $this;
    }

    public function addArguments(array $arguments):QueryBuilder  {
        $this->arguments = array_merge($this->arguments, $arguments);
        return $this;
    }

	// Prepare

	public function getResults(): Iterator {
		if (!$this->pdo) throw new mysqli_sql_exception("PDO is not defined");
        $stmt = $this->pdo->prepare($this->__toString());
        if (!$stmt) throw new mysqli_sql_exception("Error in the query : ".$this->__toString());
        $stmt->execute($this->arguments);

        if ($this->class_name === null) {
            return $stmt->getIterator();
        }

        return new PDOCursorIterator($stmt, $this->class_name);
	}

    public function getResult(): array {
		if (!$this->pdo) throw new mysqli_sql_exception("PDO is not defined");
        $stmt = $this->pdo->prepare($this->__toString());
        $stmt->execute($this->arguments);
		return  $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function __toString() {
		return "SELECT $this->select FROM $this->table_name $this->alias".
            ($this->join ?? '').
            ($this->where? " WHERE $this->where" : '').
            ($this->group ? " GROUP BY $this->group" : '').
            ($this->order ? " ORDER BY $this->order" : '').
            ($this->limit ? " LIMIT $this->limit" : '').
            ($this->offset ? " OFFSET $this->offset" : '');
	}

}