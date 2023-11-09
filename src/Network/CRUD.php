<?php

namespace JPGerber\ChaosCRUD\Network;

use Error;
use PDO, PDOException, Exception;

class CRUD
{
    private string $table;
    private array $cols;
    private array $conditions;
    private array $order;
    private string|null $dir;
    private int|null $limit;
    private array $args;
    private bool $return_last_id;
    private bool $safe;
    private string $sql;
    private $host, $name, $user, $pass;

    public function __construct($db_host = null, $db_name = null, $db_user = null, $db_pass = null)
    {
        $this->cols = [];
        $this->conditions = [];
        $this->order = [];
        $this->dir = 'ASC';
        $this->limit = null;
        $this->args = [];
        $this->return_last_id = false;
        $this->safe = true;
        $this->sql = $this->table = "";

        $this->host = $db_host ?? $_ENV['DB_HOST'] ?? $_SESSION['DB_HOST'];
        $this->name = $db_name ?? $_ENV['DB_NAME'] ?? $_SESSION['DB_NAME'];
        $this->user = $db_user ?? $_ENV['DB_USER'] ?? $_SESSION['DB_USER'];
        $this->pass = $db_pass ?? $_ENV['DB_PASS'] ?? $_SESSION['DB_PASS'];
    }

    private function connect(): object
    {
        $host = $this->host;
        $name = $this->name;
        $user = $this->user;
        $pass = $this->pass;

        $dsn = "mysql:host={$host};dbname={$name}";

        try {
            $conn = new PDO($dsn, $user, $pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            phpinfo();
            pr($e);
            die("Unable to connect: " . $e->getMessage());
        }

        return $conn;
    }

    public function table(string $table)
    {
        $this->__construct();
        $this->table = $table;
        return $this;
    }

    public function addColumn(string|null $col = null)
    {
        if ($col == null) return $this;

        array_push($this->cols, $col);
        return $this;
    }

    public function addCondition($column, $value, $operand = '=')
    {
        $key = $this->generateUniqueKey($column);

        $this->args[$column] = $value;
        $this->conditions[] = "`{$column}` {$operand} {$key}";
        return $this;
    }

    public function addOrder(string $order)
    {
        array_push($this->order, $order);
        return $this;
    }

    public function addDir(string $dir)
    {
        if ($dir !== "ASC" && $dir !== "DESC") :
            throw new Exception(die("Order direction must be either ASC or DESC."));
        endif;

        $this->dir = $dir;
        return $this;
    }

    public function addLimit(int $limit): self
    {
        if ($limit <= 0) :
            throw new Exception(die("Limit must be at least 1"));
        endif;

        $this->limit = $limit;

        return $this;
    }

    public function getLastID(bool $return_last_id)
    {
        $this->return_last_id = $return_last_id;
        return $this;
    }

    public function setSafe(bool $safe): self
    {
        $this->safe = $safe ?? true;
        return $this;
    }

    private function generateUniqueKey($column)
    {
        $key = $column;
        $count = 0;

        while (array_key_exists($key, $this->args)) {
            $count++;
            $key = $column . $count;
        }

        return ":{$key}";
    }

    public function create(array $args)
    {
        $values = [];
        $this->args = $args;
        $keys = array_keys($this->args);

        foreach ($keys as $key) :
            array_push($values, ":{$key}");
        endforeach;

        $k = "`" . implode("`, `", $keys) . "`";
        $v = implode(', ', $values);

        $this->sql = "INSERT INTO `{$this->table}` ({$k}) VALUES ({$v})";

        return $this;
    }

    public function read()
    {
        $condition = $order = $limit = null;

        if (empty($this->cols)) :
            $cols = "*";
        else :
            $cols = "`" . implode('`, `', $this->cols) . "`";
        endif;

        if (!empty($this->conditions)) :
            $condition = " WHERE ";
            $condition .= implode(" AND ", $this->conditions);
        endif;

        if (!empty($this->order)) :
            $order = " ORDER BY ";
            $order .= implode(', ', $this->order);
            $order .= " {$this->dir}";
        endif;

        if ($this->limit !== null) :
            $limit = " LIMIT {$this->limit}";
        endif;

        $this->sql = "SELECT {$cols} FROM `{$this->table}`{$condition}{$order}{$limit}";

        return $this;
    }

    public function update($args): self
    {
        $condition = null;
        $changes = [];

        foreach ($args as $key => $value) :
            array_push($changes, "`{$key}` = :update_{$key}");
            $this->args["update_{$key}"] = $value;
        endforeach;
        $changes = implode(', ', $changes);

        if (!empty($this->conditions)) :
            $condition = " WHERE ";
            $condition .= implode(" AND ", $this->conditions);
        endif;

        if ($this->limit == null && $this->safe == true) :
            $limit = $_ENV['SAFETY_LIMIT'] ?? 1;
            $limit = " LIMIT {$limit}";
        elseif ($this->limit !== null) :
            $limit = $this->limit;
            $limit = " LIMIT {$limit}";
        else :
            $limit = null;
        endif;

        $this->sql = "UPDATE `{$this->table}` SET {$changes}{$condition}{$limit}";

        return $this;
    }

    public function delete(): self
    {
        $condition = $limit = null;

        if (!empty($this->conditions)) :
            $condition = " WHERE " . implode(" AND ", $this->conditions);
        endif;

        if ($this->safe == true && $this->limit == null) :
            $this->limit = $_ENV['SAFETY_LIMIT'] ?? 1;
        elseif ($this->limit !== null) :
            $limit = " LIMIT {$this->limit}";
        endif;

        $this->sql = "DELETE FROM `{$this->table}`{$condition}{$limit}";

        return $this;
    }

    public function empty(): self
    {
        $this->sql = "TRUNCATE {$this->table};ALTER TABLE {$this->table} AUTO_INCREMENT = 1;";
        return $this;
    }

    public function custom($sql, $args)
    {
        $this->sql = validate($sql);
        $this->args = validate($args);
        return $this;
    }

    public function query()
    {
        try {
            $conn = $this->connect();
            $stmt = $conn->prepare($this->sql);

            $stmt->execute($this->args);
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($this->limit == 1 && count($res) == 1) {
                $res = $res[0];
            }

            if ($this->return_last_id == true) :
                $res = $conn->lastInsertId();
            endif;

            return $res;
        } catch (PDOException $e) {
            pr($this);
            dd($e);
        } catch (Exception $e) {
            pr($this);
            dd($e);
        } catch (Error $e) {
            pr($this);
            dd($e);
        }

        $this->__construct();
    }
}
