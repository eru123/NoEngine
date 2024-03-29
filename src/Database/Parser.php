<?php

namespace eru123\NoEngine\Database;

use eru123\NoEngine\ListType;
use \Exception;
use \PDO;
use \PDOStatement;

class Parser
{
    const keys_use      = ['use', 'db', 'database'];
    const keys_table    = ['from', 'table', 'delete', 'insert', 'update'];
    const keys_alias    = ['alias', 'as'];
    const keys_select   = ['select', 'read', 'find'];
    const keys_where    = ['where', 'condition', 'conditions'];
    const keys_order    = ['order', 'order_by'];
    const keys_limit    = ['limit', 'limit_by'];
    const keys_offset   = ['offset', 'offset_by'];
    const keys_group    = ['group', 'group_by'];
    const keys_join     = ['join', 'join_by'];
    const keys_data     = ['data', 'values'];

    /**
     * Parse Where Clause
     */
    private static function parse_where(array ...$args)
    {
        $where = ["WHERE ("];
        $values = [];
        $args_length = count($args);
        foreach ($args as $arg_key => $value) {
            $query = new ListType($value);

            foreach ($query as $k => $v) {
                if (is_int($k) && is_string($v)) {
                    $where[] = $v;
                } else if (is_string($v) || is_int($v) || is_bool($v)) {
                    $where[] = "{$k} = ?";
                    $values[] = $v;
                } else if (is_array($v)) {
                    $nv = new ListType($v);
                    if ($nv->is_array()) {
                        if ($nv->size() > 1) {
                            $where[] = "{$k} IN ({$nv->mask('?')->implode(', ')})";
                            $values = array_merge($values, $nv->toArray());
                        } else if ($nv->size() == 1) {
                            $where[] = "{$k} = ?";
                            $values = array_merge($values, $nv->toArray());
                        }
                    } else if ($nv->is_object()) {
                        foreach ($nv as $kk => $vv) {
                            $kk = strtoupper($kk);
                            if ($kk == "LIKE") {
                                $where[] = "{$k} LIKE ?";
                                $values[] = $vv;
                            } else if ($kk == "IN" && is_array($vv)) {
                                $vvv = new ListType($vv);
                                if ($vvv->size() > 1) {
                                    $where[] = "{$k} IN ({$vvv->mask('?')->implode(', ')})";
                                    $values = array_merge($values, array_values($vv));
                                } else if ($vvv->size() == 1) {
                                    $where[] = "{$k} = ?";
                                    $values = array_merge($values, array_values($vv));
                                }
                            } else if (in_array($kk, ['>', 'GT'])) {
                                $where[] = "{$k} > ?";
                                $values[] = $vv;
                            } else if (in_array($kk, ['>=', 'GTE'])) {
                                $where[] = "{$k} >= ?";
                                $values[] = $vv;
                            } else if (in_array($kk, ['<', 'LT'])) {
                                $where[] = "{$k} < ?";
                                $values[] = $vv;
                            } else if (in_array($kk, ['<=', 'LTE'])) {
                                $where[] = "{$k} <= ?";
                                $values[] = $vv;
                            } else if (in_array($kk, ['!=', '<>', 'NOT'])) {
                                $where[] = "{$k} != ?";
                                $values[] = $vv;
                            } else if (in_array($kk, ['BETWEEN', 'BETWEEN_INCLUSIVE']) && is_array($vv) && count($vv) >= 2) {
                                $where[] = "{$k} BETWEEN ? AND ?";
                                $values[] = $vv[0];
                                $values[] = $vv[1];
                            } else if (in_array($kk, ['NOT_BETWEEN', 'NOT_BETWEEN_INCLUSIVE']) && is_array($vv) && count($vv) >= 2) {
                                $where[] = "{$k} NOT BETWEEN ? AND ?";
                                $values[] = $vv[0];
                                $values[] = $vv[1];
                            } else if (in_array($kk, ['NOT_IN', 'NOT_IN_INCLUSIVE']) && is_array($vv) && count($vv) >= 2) {
                                $where[] = "{$k} NOT IN ({$nv->mask('?')->implode(', ')})";
                                $values = array_merge($values, $vv);
                            } else if (in_array($kk, ['NOT_LIKE', 'NOT_LIKE_INCLUSIVE'])) {
                                $where[] = "{$k} NOT LIKE %?%";
                                $values[] = $vv;
                            } else if (in_array($kk, ['==', 'EQUAL', 'IS', '='])) {
                                $where[] = "{$k} = ?";
                                $values[] = $vv;
                            } else {
                                throw new Exception("Invalid operator: {$k}");
                            }

                            if ($nv->getIndex() < $nv->size() - 1) {
                                $where[] = "AND";
                            }
                        }
                    } else {
                        throw new Exception("Invalid value: {$v}");
                    }
                }

                if ($query->getIndex() < $query->size() - 1) {
                    $where[] = "AND";
                }
            }

            if ($arg_key < $args_length - 1) {
                $where[] = ") OR (";
            }
        }
        $where[] = ")";
        return [
            'query' => implode(' ', $where),
            'values' => $values
        ];
    }

    /**
     * Parser's arguments handler
     */
    private static function if_valid_parse_call($query, array &$sql, array &$values = NULL, string $parse = NULL, $error = "Invalid")
    {
        if (!$query) return;

        $callError = is_array($error) ? function () use ($error) {
            throw new Exception(...$error);
        } : function () use ($error) {
            throw new Exception($error);
        };

        if (is_string($query)) {
            $sql[] = $query;
        } else if (is_array($query)) {
            $query2 = new ListType($query);
            if ($query2->is_array()) {
                $tmp = call_user_func_array([self::class, $parse], $query);
                $sql[] = $tmp['query'];
                $values = array_merge($values, $tmp['values']);
            } else if ($query2->is_object()) {
                $tmp = call_user_func_array([self::class, $parse], [$query]);
                $sql[] = $tmp['query'];
                $values = array_merge($values, $tmp['values']);
            } else {
                throw new Exception("Invalid query");
            }
        } else {
            $callError();
        }
    }

    /**
     * Get table query
     */
    private static function param_get(array &$args, array $keys)
    {
        foreach ($keys as $key) {
            if (@$args[$key]) {
                return $args[$key];
            }
        }
        return null;
    }

    /**
     * Parse join clause
     */
    private static function parse_join(array ...$args)
    {
        $sql = [];
        $values = [];
        foreach ($args as $join) {
            if (!is_array($join)) {
                throw new Exception("Invalid join");
            }

            $inner = @$join['inner'];
            $left = @$join['left'];
            $right = @$join['right'];
            $full = @$join['full'];
            $on = @$join['on'];
            $as = @$join['as'];

            if (!$inner && !$left && !$right && !$full) {
                throw new Exception("Invalid join");
            }

            if ($inner) {
                $sql[] = "INNER JOIN {$inner}";
            } else if ($left) {
                $sql[] = "LEFT JOIN {$left}";
            } else if ($right) {
                $sql[] = "RIGHT JOIN {$right}";
            } else if ($full) {
                $sql[] = "FULL JOIN {$full}";
            }

            if ($as) {
                $sql[] = "AS {$as}";
            }

            if ($on) {
                $sql[] = "ON";
                self::if_valid_parse_call($on, $sql, $values, 'parse_where', 'Invalid Query: ON Clause');
            }
        }

        return [
            'join' => implode(' ', $sql),
            'values' => $values
        ];
    }

    /**
     * Parse Insert Query
     */
    final public static function parse_insert(array $query, bool $bind = false)
    {
        $use    = self::param_get($query, self::keys_use);
        $table  = self::param_get($query, self::keys_table);
        $data   = self::param_get($query, self::keys_data);

        $sql = [];
        $values = [];

        if ($use) {
            $sql[] = "USE $use;";
        }

        if (!$table || !$data) {
            throw new Exception("Invalid INSERT query: missing table or data");
        }

        $sql[] = "INSERT";

        if (@$query['ignore'] === true) {
            $sql[] = "IGNORE";
        }

        $sql[] = "INTO {$table}";

        if (is_string($data)) {
            $sql[] = $data;
        } else if (is_array($data)) {
            $query = new ListType($data);
            if ($query->is_array()) {
                $fields = [];
                $query->map(function ($index, $row) use (&$fields) {
                    foreach ($row as $k => $v) {
                        if (!is_string($k)) {
                            throw new Exception("Invalid INSERT query: Invalid field name");
                        }

                        if (!in_array($k, $fields)) {
                            $fields[] = $k;
                        }
                    }
                });
                $sql[] = "(" . implode(', ', $fields) . ") VALUES";
                $rows = [];
                $query->map(function ($index, $row) use ($fields, &$values, &$rows) {
                    $tmp = [];
                    foreach ($fields as $col) {
                        $tmp[$col] = @$row[$col];
                    }
                    $rows[] = "(" . (new ListType($tmp))->mask('?')->implode(', ') . ")";
                    $vals = array_values($tmp);
                    $values = array_merge($values, $vals);
                });
                $sql[] = implode(', ', $rows);
            } else if ($query->is_object()) {
                $sql[] = "(" .  implode(', ', $query->keys()) . ")";
                $sql[] = "VALUES (";
                $sql[] = $query->mask('?')->implode(', ');
                $sql[] = ")";
                $values = array_merge($values, $query->values());
            } else {
                throw new Exception("Invalid data: {$data}");
            }
        } else {
            throw new Exception("Invalid data: {$data}");
        }
        $res = [
            'query' => implode(' ', $sql),
            'values' => $values
        ];

        return $bind ? self::bind($res['query'], $res['values']) : $res;
    }

    /**
     * Parse update Query
     */
    final public static function parse_update(array $query, bool $bind = false)
    {
        $use    = self::param_get($query, self::keys_use);
        $table  = self::param_get($query, self::keys_table);
        $data   = self::param_get($query, self::keys_data);
        $where  = self::param_get($query, self::keys_where);

        $sql = [];
        $values = [];

        if ($use) {
            $sql[] = "USE $use;";
        }

        if (!$table || !$data || !$where) {
            throw new Exception("Invalid UPDATE query: missing table, data or where");
        }

        $data = new ListType($data);
        if (!$data->is_object()) {
            throw new Exception("Invalid UPDATE query: data must be an array with key-value pairs");
        }

        $sql[] = "UPDATE {$table} SET";

        $updated = [];
        $data->map(function ($key, $value) use (&$updated, &$values) {
            $updated[] = "{$key} = ?";
            $values[] = $value;
        });
        $sql[] = implode(', ', $updated);

        if ($where) {
            self::if_valid_parse_call($where, $sql, $values, 'parse_where', 'Invalid Query: ON Clause', "Invalid UPDATE query: where must be an array or object");
        }

        $res = [
            'query' => implode(' ', $sql),
            'values' => $values
        ];

        return $bind ? self::bind($res['query'], $res['values']) : $res;
    }

    /**
     * Parse delete query
     */
    final public static function parse_delete(array $query, bool $bind = false)
    {
        $use    = self::param_get($query, self::keys_use);
        $table  = self::param_get($query, self::keys_table);
        $where  = self::param_get($query, self::keys_where);

        $sql = [];
        $values = [];

        if ($use) {
            $sql[] = "USE $use;";
        }

        if (!$table || !$where) {
            throw new Exception("Invalid DELETE query: missing table or where");
        }

        $sql[] = "DELETE FROM {$table}";

        if ($where) {
            self::if_valid_parse_call($where, $sql, $values, 'parse_where', "Invalid DELETE query: where must be an array or object");
        }

        $res = [
            'query' => implode(' ', $sql),
            'values' => $values
        ];

        return $bind ? self::bind($res['query'], $res['values']) : $res;
    }

    /**
     * Parse select query
     */
    final public static function parse_select(array $query, bool $bind = false)
    {
        $use    = self::param_get($query, self::keys_use);
        $table  = self::param_get($query, self::keys_table);
        $alias  = self::param_get($query, self::keys_alias);
        $select = self::param_get($query, self::keys_select);
        $where  = self::param_get($query, self::keys_where);
        $order  = self::param_get($query, self::keys_order);
        $limit  = self::param_get($query, self::keys_limit);
        $offset = self::param_get($query, self::keys_offset);
        $group  = self::param_get($query, self::keys_group);
        $join   = self::param_get($query, self::keys_join);

        $sql    = [];
        $values = [];

        if ($use) {
            $sql[] = "USE $use;";
        }

        $sql[] = "SELECT";

        if (is_string($select)) {
            $sql[] = trim($select);
        } else if (is_array($select)) {
            $select2 = new ListType($select);
            if ($select2->is_array()) {
                $sql[] = implode(', ', $select);
            } else if ($select2->is_object()) {
                $selects = [];
                foreach ($select as $field => $field_alias) {
                    if (is_string($field)) {
                        $selects[] = "{$field} AS {$field_alias}";
                    } else {
                        $selects[] = "{$field_alias}";
                    }
                }
                $sql[] = implode(', ', $selects);
            } else {
                throw new Exception("Invalid SELECT query: select must be an array or object");
            }
        } else {
            $sql[] = '*';
        }

        if (!$table) {
            throw new Exception("Invalid SELECT query: missing table");
        }

        if (is_string($table)) {
            $sql[] = "FROM {$table}";
            if ($alias) {
                $sql[] = "AS $alias";
            }
        } else if (is_array($table)) {
            $sql[] = "FROM";
            $table2 = new ListType($table);
            if ($table2->is_array() && $table2->size() >= 2) {
                $sql[] = $table[0] . " AS " . $table[1];
            } else if ($table2->is_object()) {
                foreach ($table as $key => $value) {
                    if (is_int($key) && is_string($value)) {
                        $sql[] = "$value";
                    } else if (is_string($key) && is_string($value)) {
                        $sql[] = "$key AS $value";
                    } else {
                        throw new Exception("Invalid SELECT query: table name or alias must be a string (table_name => alias)");
                    }
                    break;
                }
            } else {
                throw new Exception("Invalid SELECT query: table must be an array or object");
            }
        } else {
            throw new Exception("Invalid SELECT query: table must be a string or array");
        }

        self::if_valid_parse_call($join, $sql, $values, 'parse_join', "Invalid SELECT query: join must be a string or an array or object");
        self::if_valid_parse_call($where, $sql, $values, 'parse_where', "Invalid SELECT query: where must be a string or an array or object");

        if ($group) {
            $sql[] = "GROUP BY";
            if (is_string($group)) {
                $sql[] = $group;
            } else if (is_array($group)) {
                $group2 = new ListType($group);
                if ($group2->is_array()) {
                    $sql[] = implode(', ', $group);
                } else if ($group2->is_object()) {
                    $groups = [];
                    foreach ($group as $field => $alias) {
                        $groups[] = "{$field} AS {$alias}";
                    }
                    $sql[] = implode(', ', $groups);
                } else {
                    throw new Exception("Invalid SELECT query: group must be an array or object");
                }
            } else {
                throw new Exception("Invalid SELECT query: group must be a string or array");
            }
        }

        if ($order) {
            $sql[] = "ORDER BY";
            if (is_string($order)) {
                $sql[] = $order;
            } else if (is_array($order)) {
                $order2 = new ListType($order);
                if ($order2->is_array()) {
                    $sql[] = implode('ASC, ', $order) . " ASC";
                } else if ($order2->is_object()) {
                    $orders = [];
                    foreach ($order as $field => $direction) {
                        if (is_bool($direction)) {
                            $direction = $direction ? 'ASC' : 'DESC';
                        } else if (is_string($direction)) {
                            $direction = trim($direction);
                            $direction = strtoupper($direction);
                            if ($direction != 'ASC' && $direction != 'DESC') {
                                throw new Exception("Invalid SELECT query: order direction must be ASC or DESC");
                            }
                        } else if (!is_string($direction)) {
                            throw new Exception("Invalid SELECT query: order direction must be a string or boolean");
                        }
                        $orders[] = "{$field} {$direction}";
                    }
                    $sql[] = implode(', ', $orders);
                } else {
                    throw new Exception("Invalid SELECT query: order must be an array or object");
                }
            } else {
                throw new Exception("Invalid SELECT query: order must be a string or array");
            }
        }

        if ($limit) {
            $limit = (int) $limit;
            $sql[] = "LIMIT {$limit}";
        }

        if ($offset) {
            $offset = (int) $offset;
            $sql[] = "OFFSET {$offset}";
        }

        $res = [
            'query' => implode(' ', $sql),
            'values' => $values
        ];

        return $bind ? self::bind($res['query'], $res['values']) : $res;
    }

    /**
     * Parse query
     */
    public static function parse(array $query, bool $bind = false)
    {
        $action = @$query['action'];
        $action = trim($action);
        $action = strtoupper($action);
        $delete = @$query['delete'];
        $insert = @$query['insert'];
        $update = @$query['update'];

        if ($delete || $action == "DELETE") {
            return self::parse_delete($query, $bind);
        } else if ($insert || $action == "INSERT") {
            return self::parse_insert($query, $bind);
        } else if ($update || $action == "UPDATE") {
            return self::parse_update($query, $bind);
        }

        return self::parse_select($query, $bind);
    }

    /**
     * Bind array values to a query with question-mark (?) placeholders.
     */
    public static function bind($query, $values)
    {
        foreach ($values as $key => $value) {
            if (is_string($value)) {
                $value = str_replace('"', '\"', $value);
                $values[$key] = "\"{$value}\"";
            } else {
                $values[$key] = $value;
            }
        }
        $sql = str_replace('?', '%s', $query);
        $sql = vsprintf($sql, $values);
        return $sql;
    }
}
