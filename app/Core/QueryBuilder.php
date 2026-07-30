<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class QueryBuilder
{
    protected PDO $db;
    protected string $table;
    protected array $selects = ['*'];
    private array $wheres = [];
    private array $joins = [];
    private array $bindings = [];
    private array $groupBys = [];
    private array $havings = [];
    private ?int $limit = null;
    private ?int $offset = null;
    protected ?string $orderBy = null;

    public function __construct(PDO $db, string $table)
    {
        $this->db = $db;
        $this->table = $table;
    }



    public function select(string ...$columns): self
    {
        $this->selects = empty($columns) ? ['*'] : $columns;
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = "$type JOIN $table ON $first $operator $second";
        return $this;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function where(string $column, $operator, $value = null, string $boolean = 'AND'): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = compact('column', 'operator', 'value', 'boolean');
        $this->bindings[] = $value;
        return $this;
    }

    public function orWhere(string $column, $operator, $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        return $this->where($column, $operator, $value, 'OR');
    }

    public function whereNull(string $column, string $boolean = 'AND'): self
    {
        $this->wheres[] = ['column' => $column, 'operator' => 'IS NULL', 'value' => null, 'boolean' => $boolean, 'isNull' => true];
        return $this;
    }

    public function whereRaw(string $sql, array $bindings = [], string $boolean = 'AND'): self
    {
        $this->wheres[] = ['type' => 'raw', 'sql' => $sql, 'boolean' => $boolean];
        $this->bindings = array_merge($this->bindings, $bindings);
        return $this;
    }

    public function whereIn(string $column, array $values, string $boolean = 'AND', bool $not = false): self
    {
        if (empty($values)) {
            // where in empty array is false
            return $this->whereRaw('1 = 0', [], $boolean);
        }
        
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $operator = $not ? 'NOT IN' : 'IN';
        
        $this->wheres[] = [
            'type' => 'in',
            'column' => $column,
            'operator' => $operator,
            'placeholders' => $placeholders,
            'boolean' => $boolean
        ];
        $this->bindings = array_merge($this->bindings, array_values($values));
        
        return $this;
    }

    public function whereNotIn(string $column, array $values, string $boolean = 'AND'): self
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    public function groupBy(string ...$columns): self
    {
        $this->groupBys = array_merge($this->groupBys, $columns);
        return $this;
    }

    public function havingRaw(string $sql, array $bindings = [], string $boolean = 'AND'): self
    {
        $this->havings[] = ['sql' => $sql, 'boolean' => $boolean];
        $this->bindings = array_merge($this->bindings, $bindings);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = "$column $direction";
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    protected function buildSelect(): string
    {
        $sql = "SELECT " . implode(', ', $this->selects) . " FROM {$this->table}";

        if (!empty($this->joins)) {
            $sql .= " " . implode(' ', $this->joins);
        }

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWheres();
        }

        if (!empty($this->groupBys)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupBys);
        }

        if (!empty($this->havings)) {
            $sql .= " HAVING " . $this->buildHavings();
        }

        if ($this->orderBy) {
            $sql .= " ORDER BY {$this->orderBy}";
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    protected function buildWheres(): string
    {
        $sql = "";
        foreach ($this->wheres as $i => $where) {
            $boolean = $i > 0 ? " {$where['boolean']} " : "";
            
            if (isset($where['type']) && $where['type'] === 'raw') {
                $sql .= $boolean . $where['sql'];
            } elseif (isset($where['type']) && $where['type'] === 'in') {
                $sql .= $boolean . "{$where['column']} {$where['operator']} ({$where['placeholders']})";
            } elseif (isset($where['isNull'])) {
                $sql .= $boolean . "{$where['column']} {$where['operator']}";
            } else {
                $sql .= $boolean . "{$where['column']} {$where['operator']} ?";
            }
        }
        return $sql;
    }

    protected function buildHavings(): string
    {
        $sql = "";
        foreach ($this->havings as $i => $having) {
            $boolean = $i > 0 ? " {$having['boolean']} " : "";
            $sql .= $boolean . $having['sql'];
        }
        return $sql;
    }

    public function get(): array
    {
        $sql = $this->buildSelect();
        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll();
    }

    public function first(): ?array
    {
        $this->limit(1);
        $sql = $this->buildSelect();
        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->bindings);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function count(): int
    {
        $this->select('COUNT(*)');
        $sql = $this->buildSelect();
        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->bindings);
        return (int) $stmt->fetchColumn();
    }

    public function insert(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        return (int) $this->db->lastInsertId();
    }

    public function update(array $data): int
    {
        $sets = [];
        $bindings = [];
        
        foreach ($data as $column => $value) {
            $sets[] = "{$column} = ?";
            $bindings[] = $value;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets);
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWheres();
            $bindings = array_merge($bindings, $this->bindings);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->rowCount();
    }

    public function delete(): int
    {
        $sql = "DELETE FROM {$this->table}";
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWheres();
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->rowCount();
    }
}
