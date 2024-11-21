<?php

namespace Flender\PhpRouter\Repository;
use Iterator;
use PDO;

class Repository {

    /**
     * Name of the table in the database
     * @var string
     */
	private string $table_name;

    /**
     * Pdo instance for requests
     * @var PDO
     */
	private PDO $pdo;

    private ?string $class_name;

	public function __construct(PDO $pdo, string $table_name, ?string $class_name = null) {
        $this->pdo = $pdo;
        $this->table_name = $table_name;
        $this->class_name = $class_name;
	}

	public function createQueryBuilder(?string $alias = null): QueryBuilder {
		return new QueryBuilder($this->table_name, $alias, $this->pdo, $this->class_name);
	}

    /**
     * Function : getAll
     * -----------------
     * Get all the rows from the table
     * 
     * @return array
     * @throws \mysqli_sql_exception
     */
	public function getAll(): array {
		return $this->createQueryBuilder()->getResults();
	}

    /**
     * Function : find
     * ---------------
     * Find rows with the given options
     * 
     * @param array $options
     * @return array
     */
	public function find_one(array $options=[]): array {

        $t = array();
        foreach($options as $key => $value) {
            $t[$key] = "$key = :$key";
        }

		return $this->createQueryBuilder()->where(join(' AND ', $t))->addArguments($options)->getResult();
	}

    public function getNumberTotalRows(): int {
        return $this->createQueryBuilder()->select("COUNT(*) as total")->getResult()['total'];
    }

    public function insert(array $data):bool {
        $this->pdo->prepare("INSERT INTO {$this->table_name} (".implode(',', array_keys($data)).") VALUES (:".implode(',:', array_keys($data)).")")->execute($data);
        return true;
    }

    public function update(array $data, array $where):bool {
        $this->pdo->prepare("UPDATE {$this->table_name} SET ".implode(',', array_map(function($key) { return "{$key} = :{$key}"; }, array_keys($data)))." WHERE ".implode(' AND ', array_map(function($key) { return "{$key} = :{$key}"; }, array_keys($where))))->execute(array_merge($data, $where));
        return true;
    }

    public function getLastInsertId():int {
        return $this->pdo->lastInsertId($this->table_name);
    }

}