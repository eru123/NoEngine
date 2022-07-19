<?php

namespace eru123\NoEngine\Database;

use \PDO;
use \PDOException;

/**
 * Extended PDO class.
 */
class XPDO extends PDO
{
    /**
     * Constructor.
     */
    public function __construct(...$args)
    {
        parent::__construct(...$args);

        if($this->getAttribute(PDO::ATTR_PERSISTENT)) {
            throw new PDOException('XPDO Instance only allowed non-persistent connection only');
        }

        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, [XPDOStatement::class, [$this]]);
    }

    /**
     * Alias of PDO::prepare with some additional features.
     */
    final public function prep(...$args): XPDOStatement
    {
        if (count($args) > 0 && is_string($args[0])) {
            $sth = $this->prepare(...$args);
        } else if (count($args) > 0 && is_array($args[0])) {
            $sql = Parser::parse($args[0], false);
            unset($args[0]);
            $sth = $this->prepare($sql['query'], ...$args);
            $sth->bind($sql['values']);
        } else {
            throw new \Exception('Invalid arguments.');
        }

        return $sth;
    }
}
