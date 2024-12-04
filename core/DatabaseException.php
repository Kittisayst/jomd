<?php
// DatabaseException.php
class DatabaseException extends Exception
{
    protected $query;
    protected $params;

    public function __construct(string $message, string $query = '', array $params = [], int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->query = $query;
        $this->params = $params;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
